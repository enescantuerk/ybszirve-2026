<?php
/* Template Name: Rezervasyon Sayfası */

// Admin işlemleri için admin-ajax yerine sayfa üstünden JSON endpoint
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['ybs_front_admin_action']) &&
    $_POST['ybs_front_admin_action'] === '1'
) {
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Yetkisiz işlem.']);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';
    $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';

    if (function_exists('ybs_setup_database')) {
        ybs_setup_database();
    }

    switch ($action) {
        case 'ybs_toggle_system_status':
            $current_status = get_option('ybs_system_sold_out', '0');
            $new_status = ($current_status === '1') ? '0' : '1';
            update_option('ybs_system_sold_out', $new_status);
            wp_send_json_success(['new_status' => $new_status]);
            break;

        case 'ybs_admin_manual_bulk_add':
            $seat_ids = isset($_POST['seats']) ? $_POST['seats'] : [];
            if (!is_array($seat_ids)) {
                $seat_ids = explode(',', (string) $seat_ids);
            }
            if (empty($seat_ids)) {
                wp_send_json_error(['message' => 'Koltuk seçilmedi.']);
            }

            $cat = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'club';
            $note = isset($_POST['note']) ? sanitize_text_field(wp_unslash($_POST['note'])) : '';
            $note = trim($note);
            if ($note === '') {
                wp_send_json_error(['message' => 'Not / İsim alanı boş olamaz.']);
            }
            $color = isset($_POST['color']) ? sanitize_text_field($_POST['color']) : '#3b82f6';
            $is_multi = isset($_POST['is_multi']) && $_POST['is_multi'] === '1';

            $multi_seats = get_option('ybs_multi_seats', []);
            if (!is_array($multi_seats)) $multi_seats = [];

            foreach ($seat_ids as $seat_id) {
                $seat_id = sanitize_text_field($seat_id);
                if ($seat_id === '') continue;

                if ($is_multi && !in_array($seat_id, $multi_seats, true)) {
                    $multi_seats[] = $seat_id;
                }

                // Var olan kayıtları silme, sadece yeni bir grup kaydı ekle
                $display_name = $note;
                $fake_email = 'misafir_' . strtolower(str_replace('-', '_', $seat_id)) . '_' . rand(100, 999) . '@ybszirve.local';
                $token = md5(uniqid($seat_id . mt_rand(), true));

                $inserted = $wpdb->insert($table, [
                    'seat_id' => $seat_id,
                    'user_name' => $display_name,
                    'user_email' => $fake_email,
                    'user_phone' => '-',
                    'category' => $cat,
                    'note' => $display_name,
                    'color' => $color,
                    'status' => 'approved',
                    'is_checked_in' => 0,
                    'kvkk_sponsor_izin' => ($cat === 'club' ? 1 : 0),
                    'bilet_token' => $token,
                    'reservation_date' => current_time('mysql')
                ]);

                if ($inserted === false) {
                    wp_send_json_error(['message' => 'DB Hatası: ' . $wpdb->last_error]);
                }
            }

            update_option('ybs_multi_seats', array_values(array_unique($multi_seats)));
            wp_send_json_success(['message' => 'Başarıyla kaydedildi.']);
            break;

        case 'ybs_admin_toggle_multi':
            $seat_id = isset($_POST['seat_id']) ? sanitize_text_field($_POST['seat_id']) : '';
            $do = isset($_POST['do']) ? sanitize_text_field($_POST['do']) : '';
            if ($seat_id === '') {
                wp_send_json_error(['message' => 'Koltuk bilgisi eksik.']);
            }

            $multi = get_option('ybs_multi_seats', []);
            if (!is_array($multi)) $multi = [];

            if ($do === 'add_multi' && !in_array($seat_id, $multi, true)) {
                $multi[] = $seat_id;
            } elseif ($do === 'remove_multi') {
                $multi = array_values(array_diff($multi, [$seat_id]));
            }

            update_option('ybs_multi_seats', array_values($multi));
            wp_send_json_success();
            break;

        case 'ybs_admin_delete_single':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id <= 0) {
                wp_send_json_error(['message' => 'Geçersiz kayıt.']);
            }
            $wpdb->delete($table, ['id' => $id]);
            wp_send_json_success();
            break;

        case 'ybs_admin_delete_seat_all':
            $seat_id = isset($_POST['seat_id']) ? sanitize_text_field($_POST['seat_id']) : '';
            if ($seat_id === '') {
                wp_send_json_error(['message' => 'Koltuk bilgisi eksik.']);
            }
            $wpdb->delete($table, ['seat_id' => $seat_id]);
            $multi = get_option('ybs_multi_seats', []);
            if (is_array($multi) && in_array($seat_id, $multi, true)) {
                $multi = array_values(array_diff($multi, [$seat_id]));
                update_option('ybs_multi_seats', $multi);
            }
            wp_send_json_success();
            break;

        case 'ybs_admin_delete_all_seats':
            $wpdb->query("TRUNCATE TABLE $table");
            wp_send_json_success();
            break;

        case 'ybs_reorganize_seats':
            // Veri JSON string olarak gönderilir (max_input_vars limitini aşmamak için)
            $moves_raw = [];
            if (!empty($_POST['moves_json'])) {
                $decoded = json_decode(wp_unslash($_POST['moves_json']), true);
                if (is_array($decoded)) $moves_raw = $decoded;
            }
            if (empty($moves_raw)) {
                wp_send_json_error(['message' => 'Taşıma listesi boş veya geçersiz.']);
            }
            $success_count = 0;
            foreach ($moves_raw as $move) {
                $db_id    = intval(isset($move['id'])        ? $move['id']        : 0);
                $new_seat = sanitize_text_field(isset($move['newSeatId']) ? $move['newSeatId'] : '');
                if ($db_id <= 0 || empty($new_seat)) continue;
                $arch_row = $wpdb->get_row($wpdb->prepare("SELECT archived_from_seat, seat_id FROM $table WHERE id = %d", $db_id), ARRAY_A);
                if (!$arch_row) {
                    continue;
                }
                if (!empty($arch_row['archived_from_seat']) || (isset($arch_row['seat_id']) && strpos((string) $arch_row['seat_id'], 'ARSV-') === 0)) {
                    continue;
                }
                $updated = $wpdb->update($table, ['seat_id' => $new_seat], ['id' => $db_id]);
                if ($updated !== false) $success_count++;
            }
            // Yeni çoklu satış koltukları
            $new_multis = [];
            if (!empty($_POST['newMultiSeats_json'])) {
                $decoded_m = json_decode(wp_unslash($_POST['newMultiSeats_json']), true);
                if (is_array($decoded_m)) $new_multis = $decoded_m;
            }
            if (!empty($new_multis)) {
                $multi = get_option('ybs_multi_seats', []);
                if (!is_array($multi)) $multi = [];
                foreach ($new_multis as $sid) {
                    $sid = sanitize_text_field($sid);
                    if ($sid !== '' && !in_array($sid, $multi, true)) {
                        $multi[] = $sid;
                    }
                }
                update_option('ybs_multi_seats', array_values($multi));
            }
            wp_send_json_success(['message' => $success_count . ' koltuk taşındı.', 'count' => $success_count]);
            break;

        case 'ybs_redistribute_multi':
            $moves_raw = [];
            if (!empty($_POST['moves_json'])) {
                $decoded = json_decode(wp_unslash($_POST['moves_json']), true);
                if (is_array($decoded)) $moves_raw = $decoded;
            }
            if (empty($moves_raw)) {
                wp_send_json_error(['message' => 'Taşıma listesi boş.']);
            }
            $success_count = 0;
            foreach ($moves_raw as $move) {
                $db_id    = intval(isset($move['id'])        ? $move['id']        : 0);
                $new_seat = sanitize_text_field(isset($move['newSeatId']) ? $move['newSeatId'] : '');
                if ($db_id <= 0 || empty($new_seat)) continue;
                $arch_row = $wpdb->get_row($wpdb->prepare("SELECT archived_from_seat, seat_id FROM $table WHERE id = %d", $db_id), ARRAY_A);
                if (!$arch_row) {
                    continue;
                }
                if (!empty($arch_row['archived_from_seat']) || (isset($arch_row['seat_id']) && strpos((string) $arch_row['seat_id'], 'ARSV-') === 0)) {
                    continue;
                }
                $updated = $wpdb->update($table, ['seat_id' => $new_seat], ['id' => $db_id]);
                if ($updated !== false) $success_count++;
            }
            wp_send_json_success(['message' => $success_count . ' kayıt yeniden dağıtıldı.', 'count' => $success_count]);
            break;

        case 'ybs_archive_club_group':
            $club_note = isset( $_POST['club_note'] ) ? sanitize_text_field( wp_unslash( $_POST['club_note'] ) ) : '';
            if ( $club_note === '' ) {
                wp_send_json_error( [ 'message' => 'Grup notu boş.' ] );
            }
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, seat_id FROM $table WHERE note = %s AND archived_from_seat IS NULL AND category = %s",
                    $club_note,
                    'club'
                )
            );
            if ( empty( $rows ) ) {
                wp_send_json_error( [ 'message' => 'Bu notla eşleşen kulüp (club) kaydı yok veya zaten arşivlenmiş.' ] );
            }
            $physical_seats = [];
            foreach ( $rows as $r ) {
                if ( strpos( (string) $r->seat_id, 'ARSV-' ) === 0 ) {
                    continue;
                }
                $physical_seats[] = $r->seat_id;
                $wpdb->update(
                    $table,
                    [
                        'seat_id'            => 'ARSV-' . (int) $r->id,
                        'archived_from_seat' => $r->seat_id,
                        'archived_at'        => current_time( 'mysql' ),
                    ],
                    [ 'id' => (int) $r->id ],
                    [ '%s', '%s', '%s' ],
                    [ '%d' ]
                );
            }
            $physical_seats = array_unique( $physical_seats );
            $multi            = get_option( 'ybs_multi_seats', [] );
            if ( is_array( $multi ) && ! empty( $physical_seats ) ) {
                foreach ( $physical_seats as $ps ) {
                    $cnt = (int) $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM $table WHERE seat_id = %s AND archived_from_seat IS NULL",
                            $ps
                        )
                    );
                    if ( $cnt === 0 && in_array( $ps, $multi, true ) ) {
                        $multi = array_values( array_diff( $multi, [ $ps ] ) );
                    }
                }
                update_option( 'ybs_multi_seats', $multi );
            }
            wp_send_json_success(
                [
                    'message' => count( $rows ) . ' kayıt arşive alındı; koltuklar bireysel rezervasyona açıldı.',
                    'count'   => count( $rows ),
                ]
            );
            break;
    }

    wp_send_json_error(['message' => 'Geçersiz işlem.']);
}

// 1. Zoom engelleme (Pinch-to-zoom kapalı)
function ybs_reservation_viewport() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
}
add_action('wp_head', 'ybs_reservation_viewport', 1);

get_header(); 

$is_admin = current_user_can('manage_options');

// SİSTEM DURUMUNU KONTROL ET (1 = Kapalı/Sold Out, 0 = Açık)
$is_sold_out = get_option('ybs_system_sold_out', '0') == '1';

// Verileri Çek
$status_data = function_exists('get_reservation_status') ? get_reservation_status() : ['booked'=>[], 'multi'=>[]];
if(!$status_data) $status_data = ['booked'=>[], 'multi'=>[]];

$admin_full_data = [];
$raw_admin_data = []; 
if($is_admin && function_exists('get_admin_hall_data')) {
    $raw_admin_data = get_admin_hall_data();
    if(isset($raw_admin_data['reservations'])) {
        $last_status = [];
        foreach($raw_admin_data['reservations'] as $seat_id => $rows) {
            $last_status[$seat_id] = end($rows); 
        }
        $admin_full_data = $last_status;
    }
}

if($is_admin){
    ?>
    <style>
        header, footer, #wpadminbar { display: none !important; } 
        html, body { margin: 0 !important; padding: 0 !important; height: 100%; }
        
        /* Sayfa kaydırmayı sadece bilgisayarlarda uygula */
        @media (min-width: 901px) { html, body { overflow: hidden; } }
        /* Mobilde serbest bırak */
        @media (max-width: 900px) { html, body { overflow: auto !important; height: auto !important; } }
    </style>
    <?php
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="reservation-page-wrapper">

    <div id="reservation-layout" class="<?php echo $is_admin ? 'mode-admin' : 'mode-user'; ?> <?php echo ($is_sold_out && !$is_admin) ? 'is-sold-out' : ''; ?>">

        <style>
            :root {
                --seat-w: 28px; --seat-h: 34px; --seat-font: 10px;
                --gap-size: 4px; --row-gap: 8px;
                --c-bg: #f3f4f6; --c-panel: #ffffff; --c-border: #e5e7eb; --c-text: #1f2937;
                --c-standard: #ef4444; --c-selected: #10b981; 
                --sidebar-w: 380px;
            }

            .reservation-page-wrapper {
                background-color: var(--c-bg);
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                color: var(--c-text);
                width: 100%; min-height: 100vh; box-sizing: border-box;
            }

            .mode-admin { display: grid; grid-template-columns: 1fr var(--sidebar-w); height: 100vh; width: 100vw; overflow: hidden; }
            
            .mode-user { 
                max-width: 1400px; margin: 0 auto; 
                padding-top: 120px; padding-bottom: 50px; padding-left: 20px; padding-right: 20px;
                display: grid; grid-template-columns: 1fr var(--sidebar-w); gap: 30px;
                height: 85vh; min-height: 700px; 
            }

            .map-box {
                background: #fff; position: relative; overflow: hidden;
                display: flex; flex-direction: column; border: 1px solid var(--c-border);
            }
            .mode-admin .map-box { border: none; border-right: 1px solid var(--c-border); }
            .mode-user .map-box { border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }

            .map-header {
                padding: 15px 20px; background: rgba(255,255,255,0.95);
                border-bottom: 1px solid var(--c-border);
                display: flex; justify-content: space-between; align-items: center;
                z-index: 10; min-height: 60px;
            }
            
            .map-title-wrapper { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
            .map-title { margin: 0; font-size: 18px; font-weight: 800; color: #111; }
            
            .btn-back-home {
                display: inline-flex; align-items: center; justify-content: center; gap: 6px;
                padding: 8px 14px; background: #f3f4f6; color: #4b5563;
                text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 700;
                border: 1px solid #d1d5db; transition: all 0.2s;
            }
            .btn-back-home:hover { background: #e5e7eb; color: #111827; border-color: #9ca3af; }

            .btn-check-ticket {
                display: inline-flex; align-items: center; justify-content: center; gap: 6px;
                padding: 8px 14px; background: #fff; color: #3b82f6;
                text-decoration: none; border-radius: 6px; font-size: 13px; font-weight: 700;
                border: 1px solid #bfdbfe; transition: all 0.2s; cursor: pointer;
            }
            .btn-check-ticket:hover { background: #eff6ff; border-color: #93c5fd; }

            .seat-map-viewport {
                flex: 1; width: 100%; height: 100%; 
                overflow: hidden; cursor: grab;
                display: flex; justify-content: center; align-items: center;
                background-image: radial-gradient(#e1e4e8 1px, transparent 1px); background-size: 20px 20px;
                touch-action: none; position: relative;
            }
            .seat-map-viewport:active { cursor: grabbing; }
            .seat-map { display: flex; flex-direction: column; gap: var(--row-gap); padding: 150px; align-items: center; transition: transform 0.1s; }

            .sidebar-box { background: #fff; display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--c-border); width: 100%; }
            .mode-admin .sidebar-box { border: none; }
            .mode-user .sidebar-box { border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
            .sidebar-header { padding: 20px; background: #f9fafb; border-bottom: 1px solid var(--c-border); height: 60px; display:flex; align-items:center; }
            .sidebar-header h3 { margin: 0; font-size: 14px; font-weight: 800; text-transform: uppercase; color: #6b7280; letter-spacing: 1px; }
            .sidebar-content { flex: 1; padding: 25px; overflow-y: auto; }
            .sidebar-footer { padding: 20px; background: #fff; border-top: 1px solid var(--c-border); }

            .zoom-controls { position: absolute; bottom: 30px; left: 30px; display: flex; gap: 8px; z-index: 50; }
            .z-btn { width: 36px; height: 36px; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; font-size: 18px; cursor: pointer; display:flex; align-items:center; justify-content:center;}

            .row { display: flex; justify-content: center; align-items: flex-end; }
            .row-label { width: 30px; font-weight: bold; font-size: 11px; color: #ccc; text-align: center; margin-bottom: 5px; }
            .wing { display: flex; gap: var(--gap-size); align-items: flex-end; }
            .wing.left { transform: rotate(-4deg) translateY(8px); margin-right: 40px; }
            .wing.right { transform: rotate(4deg) translateY(8px); margin-left: 40px; }
            .gap-large { width: 300px; height: 10px; flex-shrink: 0; }

            .seat { width: var(--seat-w); height: var(--seat-h); background: #fff; border: 1px solid #d1d5db; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: var(--seat-font); font-weight: 700; color: #6b7280; cursor: pointer; user-select: none; border-bottom-width: 4px; transition: 0.1s; flex-shrink: 0; }
            .seat:hover { transform: translateY(-3px); z-index: 5; border-color: #9ca3af; }
            .seat.selected { background: var(--c-selected) !important; color: #fff !important; border-color: #059669 !important; transform: translateY(-2px); }
            
            .seat.booked { cursor: default; opacity: 1; color: #fff; border-bottom-width: 2px; }
            .mode-user .seat.booked { background: var(--c-standard); border-color: #b91c1c; }
            .mode-admin .seat.booked { cursor: pointer; } 

            /* Ziyaretçide sadece görsel doluluk (overbook koltuklarında boş gibi görünmesin) */
            .seat.visual-booked {
                background: var(--c-standard);
                border-color: #b91c1c;
                color: #fff;
                border-bottom-width: 2px;
            }
            
            .mode-admin .seat.multi-active { box-shadow: 0 0 0 2px #f59e0b; border-color: #d97706; z-index: 2; }
            .mode-admin .seat.multi-empty { background: #fff; color: #f59e0b; border: 2px dashed #f59e0b; border-bottom-width: 4px; box-shadow: none; }

            /* GRUP HOVER VURGULAMA */
            @keyframes group-pulse {
                0%   { box-shadow: 0 0 0 3px #fff, 0 0 8px 3px rgba(250,204,21,0.7); }
                50%  { box-shadow: 0 0 0 3px #fff, 0 0 16px 7px rgba(250,204,21,1); }
                100% { box-shadow: 0 0 0 3px #fff, 0 0 8px 3px rgba(250,204,21,0.7); }
            }
            .mode-admin .seat.group-highlight {
                outline: 2px solid #facc15 !important;
                outline-offset: 1px;
                box-shadow: 0 0 0 3px #fff, 0 0 10px 4px rgba(250,204,21,0.8) !important;
                z-index: 4 !important;
                transform: translateY(-2px) !important;
                animation: group-pulse 1.2s ease-in-out infinite;
            }
            .mode-admin .seat.group-hover-source {
                outline: 2px solid #fbbf24 !important;
                outline-offset: 1px;
                box-shadow: 0 0 0 3px #fff, 0 0 18px 7px rgba(250,204,21,1) !important;
                z-index: 6 !important;
                transform: translateY(-3px) !important;
                animation: group-pulse 0.8s ease-in-out infinite;
            }

            #group-tooltip {
                display: none;
                position: fixed;
                z-index: 9999;
                background: #1f2937;
                color: #fff;
                border-radius: 10px;
                padding: 10px 14px;
                font-size: 12px;
                line-height: 1.6;
                box-shadow: 0 8px 24px rgba(0,0,0,0.35);
                pointer-events: none;
                max-width: 280px;
                min-width: 160px;
            }
            .mode-admin #group-tooltip { pointer-events: auto; }
            #group-tooltip .gt-title { font-weight: 700; font-size: 13px; margin-bottom: 7px; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 5px; color: #fde68a; }
            #group-tooltip .gt-count { font-size: 11px; color: #9ca3af; margin-bottom: 6px; }
            #group-tooltip .gt-seats { display: flex; flex-wrap: wrap; gap: 4px; }
            #group-tooltip .gt-seat-tag { background: rgba(255,255,255,0.1); border-radius: 4px; padding: 2px 6px; font-size: 11px; font-weight: 600; }
            #group-tooltip .gt-seat-tag.current { background: rgba(250,204,21,0.25); color: #fde68a; }
            #group-tooltip .gt-archive-wrap { margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.12); }
            #group-tooltip .gt-btn-archive { width: 100%; padding: 8px 10px; border-radius: 6px; border: none; background: #6366f1; color: #fff; font-weight: 800; font-size: 11px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.04em; }
            #group-tooltip .gt-btn-archive:hover { background: #4f46e5; }
            #group-tooltip .gt-archive-hint { display: block; margin-top: 6px; font-size: 9px; color: #9ca3af; line-height: 1.35; }

            /* SİSTEM KAPALI (SOLD OUT) CSS MÜDAHALELERİ */
            .is-sold-out .seat { cursor: not-allowed !important; }
            .is-sold-out .seat:hover { transform: none !important; border-color: #d1d5db !important; z-index: 1 !important; }

            .stage-box { margin-top: 60px; width: 100%; display: flex; justify-content: center; pointer-events: none; }
            .stage-visual { width: 600px; height: 70px; background: #e5e7eb; border-top: 5px solid var(--c-selected); border-radius: 50% 50% 0 0 / 25px; display: flex; align-items: center; justify-content: center; font-weight: 900; letter-spacing: 5px; color: #9ca3af; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

            .smart-legend { display: flex; gap: 15px; flex-wrap: wrap; justify-content: flex-end; align-items: center; }
            .smart-legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; color: #555; background: #f3f4f6; padding: 4px 8px; border-radius: 6px; }
            .smart-dot { width: 10px; height: 10px; border-radius: 50%; }

            #overbook-info-btn {
                display: inline-flex; align-items: center; gap: 5px;
                padding: 6px 11px; background: #fff; color: #6b7280;
                border: 1px solid #e5e7eb; border-radius: 6px;
                font-size: 12px; font-weight: 600; cursor: pointer;
                transition: all 0.2s;
            }
            #overbook-info-btn:hover { background: #f9fafb; color: #374151; border-color: #d1d5db; }
            #overbook-info-popup {
                display: none;
                position: absolute; right: 0; top: calc(100% + 8px);
                width: 290px; background: #fff;
                border: 1px solid #e5e7eb; border-radius: 14px;
                padding: 18px; box-shadow: 0 12px 30px rgba(0,0,0,0.12);
                z-index: 200; text-align: left;
                max-height: 260px; overflow-y: auto;
            }
            @media (max-width: 900px) {
                #overbook-info-popup { display: none !important; }
            }

            .form-group { margin-bottom: 15px; text-align: left; }
            .form-group label { display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 5px; text-transform: uppercase; }
            .form-control { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
            
            .btn-primary { width: 100%; padding: 14px; background: #111827; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s;}
            .btn-primary:hover:not(:disabled) { background: #1f2937; }
            .btn-primary:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }
            
            .btn-success { width: 100%; padding: 14px; background: #10b981; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.2s;}
            .btn-success:hover { background: #059669; }

            .btn-danger { background: #fee2e2; color: #b91c1c; width: 100%; padding: 10px; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight:bold;}
            .btn-warning { background: #fef3c7; color: #b45309; width: 100%; padding: 10px; border: 1px solid #fde68a; border-radius: 6px; cursor: pointer; margin-top: 10px; font-weight:bold;}
            .ticket-card { background: #fff; border: 2px dashed var(--c-selected); border-radius: 8px; padding: 15px; margin-bottom: 20px; background-color: rgba(16, 185, 129, 0.05); }
            .seat-tag { background: #fff; border: 1px solid var(--c-selected); color: var(--c-selected); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 800; }
            
            .admin-stat-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 15px; border: 1px solid #eee; }
            .admin-stat-num { font-size: 24px; font-weight: 800; color: #333; display: block; }
            .admin-stat-label { font-size: 11px; text-transform: uppercase; color: #888; font-weight: 700; }
            
            .multi-list-item { background: #fff; border: 1px solid #e5e7eb; padding: 8px; margin-bottom: 5px; border-radius: 4px; font-size: 12px; display: flex; justify-content: space-between; align-items: center;}
            .multi-list-item button { background: none; border: none; color: red; cursor: pointer; font-weight: bold; }

            /* Modal CSS */
            .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;}
            .modal-content { background: #fff; width: 100%; max-width: 450px; border-radius: 16px; padding: 30px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); position: relative; max-height: 90vh; overflow-y: auto;}
            .modal-close { position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 28px; color: #9ca3af; cursor: pointer; line-height: 1; }
            .modal-close:hover { color: #111827; }

            @media (max-width: 900px) {
                .reservation-page-wrapper { display: block; padding: 15px; height: auto; min-height: auto; }
                .mode-user { margin-top: 60px; padding-top: 16px; display: block; height: auto; }
                .mode-admin { display: block; height: auto; overflow: auto; padding-top: 20px; }
                .map-box { width: 100%; height: 55vh; min-height: 420px; margin-bottom: 24px; border-radius: 16px; border: 1px solid var(--c-border); }
                .sidebar-box { width: 100%; height: auto; border-radius: 16px; border: 1px solid var(--c-border); margin-bottom: 40px; }
                .seat-map-viewport { touch-action: none; }
                
                .map-header { flex-direction: row; align-items: center; justify-content: space-between; gap: 8px; padding: 12px; }
                .map-title-wrapper { flex-direction: row; align-items: center; width: auto; flex: 1; gap: 8px; }
                .btn-check-ticket { width: auto; }
                .mode-user .btn-back-home { display: none; }
                .map-title { display: none; }
                .mode-user .smart-legend { display: flex; justify-content: flex-end; width: auto; margin-top: 0; }
                .mode-admin .smart-legend { display: none; }
            }
        </style>

        <div class="map-box">
            <div class="map-header">
                <div class="map-title-wrapper">
                    <a href="<?php echo site_url(); ?>" class="btn-back-home">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                        Ana Sayfa
                    </a>
                    
                    <?php if(!$is_admin): ?>
                    <button class="btn-check-ticket" onclick="openTicketModal()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Biletimi Sorgula
                    </button>
                    <?php endif; ?>
                    
                    <h2 class="map-title"><?php echo $is_admin ? 'Admin Haritası' : 'Koltuk Seçimi'; ?></h2>
                </div>
                
                <div class="smart-legend" id="smart-legend">
                    <?php if(!$is_admin): ?>
                    <div id="overbook-info-wrap" style="position:relative;">
                        <button id="overbook-info-btn" onclick="toggleOverbookInfo(event)" title="Overbook Koltuğu Nedir?">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            Paylaşımlı Koltuk
                        </button>
                        <div id="overbook-info-popup">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                <span style="font-size:13px; font-weight:800; color:#1f2937;">Paylaşımlı (Overbook) Koltuk Nedir?</span>
                                <button onclick="document.getElementById('overbook-info-popup').style.display='none'" style="background:none; border:none; font-size:20px; color:#9ca3af; cursor:pointer; line-height:1; padding:0;">&times;</button>
                            </div>
                            <div style="font-size:13px; color:#4b5563; line-height:1.65;">
                                <p style="margin:0 0 10px;">Bazı koltuklar <strong style="color:#b45309;">paylaşımlı</strong> olarak açılmıştır. Bu koltuklara birden fazla kişi bilet alabilir.</p>
                                <p style="margin:0 0 10px;">Salon kapasitesine göre bilet sahipleri <b>belirlenen oturma düzenine</b> göre yerleştirilir.</p>
                                <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:10px; display:flex; gap:8px; align-items:flex-start;">
                                    <span style="font-size:16px; flex-shrink:0;">🪑</span>
                                    <span style="font-size:12px; color:#92400e;">Paylaşımlı koltuklarda standart koltuklar gibi tek kişilik rezervasyon yapılır; ancak bu koltuğu başkaları da seçebilir.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="seat-map-viewport" id="map-viewport">
                <div id="seat-map" class="seat-map"></div>
            </div>

            <div id="group-tooltip">
                <div class="gt-title" id="gt-title-text"></div>
                <div class="gt-count" id="gt-count-text"></div>
                <div class="gt-seats" id="gt-seats-list"></div>
                <?php if ( $is_admin ) : ?>
                <div id="gt-archive-wrap" class="gt-archive-wrap" style="display:none;">
                    <button type="button" id="gt-btn-archive" class="gt-btn-archive">Arşive taşı</button>
                    <span class="gt-archive-hint">Yalnızca <strong>club</strong> kategorisindeki kayıtlar. Koltuklar boşalır; veri silinmez.</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="zoom-controls">
                <button class="z-btn" id="zi">+</button>
                <button class="z-btn" id="zo">-</button>
                <button class="z-btn" id="zr">⟳</button>
            </div>
        </div>

        <div class="sidebar-box">
            
            <?php if($is_admin): ?>
            <div class="sidebar-header"><h3>YÖNETİM PANELİ</h3></div>
            <div class="sidebar-content">
                
                <div style="background:#f9fafb; padding:15px; border-radius:8px; border:1px solid #e5e7eb; margin-bottom:20px; text-align:center;">
                    <strong style="display:block; margin-bottom:10px; font-size:13px; color:#374151; text-transform:uppercase;">Ziyaretçi Bilet Satışları</strong>
                    <button id="btn-toggle-system" onclick="toggleSystemStatus()" style="width:100%; padding:12px; border-radius:6px; font-weight:bold; cursor:pointer; border:2px solid <?php echo $is_sold_out ? '#fecaca' : '#a7f3d0'; ?>; background:<?php echo $is_sold_out ? '#fef2f2' : '#ecfdf5'; ?>; color:<?php echo $is_sold_out ? '#b91c1c' : '#10b981'; ?>;">
                        <?php echo $is_sold_out ? '🛑 ŞU AN KAPALI (Açmak İçin Tıkla)' : '✅ ŞU AN AÇIK (Kapatmak İçin Tıkla)'; ?>
                    </button>
                    <p style="font-size:11px; color:#6b7280; margin-top:10px; line-height:1.4;">Sistemi kapattığınızda ziyaretçiler sağ panelde <b>"Biletler Tükendi"</b> yazısını görür, haritada koltuk seçemezler.</p>
                </div>
                <hr style="border:0; border-top:1px solid #e5e7eb; margin-bottom:20px;">

                <div id="admin-view-empty">
                    <div style="display:flex; gap:10px; margin-bottom:15px;">
                        <div class="admin-stat-box" style="flex:1; margin-bottom:0;">
                            <span class="admin-stat-num" id="stat-total">0</span>
                            <span class="admin-stat-label">Dolu Koltuk</span>
                        </div>
                        <div class="admin-stat-box" style="flex:1; margin-bottom:0; background:#f0fdf4; border-color:#bbf7d0;">
                            <span class="admin-stat-num" id="stat-reg-count" style="color:#166534;">0</span>
                            <span class="admin-stat-label" style="color:#15803d;">Toplam Kayıt</span>
                        </div>
                    </div>
                    <div id="admin-stat-detail" style="font-size:12px; color:#4b5563; text-align:left; line-height:1.55; margin-bottom:14px; padding:10px 12px; background:#fff; border-radius:8px; border:1px solid #e5e7eb;">
                        <div><strong>Toplam kayıt:</strong> <span id="stat-detail-toplam">0</span></div>
                        <div style="margin-top:4px;"><strong>Bireysel kayıt:</strong> <span id="stat-detail-bireysel">0</span> <span style="color:#9ca3af; font-size:11px;">(standart)</span></div>
                        <div style="margin-top:4px;"><strong>Çoklu satış koltuğu:</strong> <span id="stat-detail-multi">0</span></div>
                        <div style="margin-top:4px;"><strong>Boş koltuk:</strong> <span id="stat-detail-bos">0</span></div>
                    </div>
                    
                    <p style="font-size:13px; color:#666; margin-top:15px; text-align:center;">İşlem yapmak için haritadan koltuk seçin.</p>
                    
                    <button onclick="openReorganizeModal()" style="margin-top:20px; width:100%; padding:12px; border-radius:6px; font-weight:700; font-size:14px; cursor:pointer; border:2px solid #1d4ed8; background:#eff6ff; color:#1d4ed8; display:block;">
                        🔀 Koltukları Yeniden Düzenle
                    </button>

                    <button onclick="openRedistributeModal()" style="margin-top:8px; width:100%; padding:12px; border-radius:6px; font-weight:700; font-size:14px; cursor:pointer; border:2px solid #0891b2; background:#ecfeff; color:#0891b2; display:block;">
                        ⚖️ Koltuk başına çoklu yükü dengle
                    </button>
                    <p style="font-size:11px; color:#6b7280; margin:6px 0 0; line-height:1.45;">Çoklu koltuklardaki kayıtları mümkün olduğunca eşit yayar; <strong>koltuk başına en fazla kaç kişi</strong> kalacağını minimize eder (çoklu koltuk sayısını değiştirmez).</p>

                    <button onclick="clearAllSeats()" class="btn-danger" style="margin-top:10px; border:2px solid #b91c1c; padding:12px; font-size:14px; background:#fef2f2;">
                        ⚠️ TÜM SALONU BOŞALT
                    </button>
                </div>
                
                <div id="admin-view-selection" style="display:none;">
                    <div class="ticket-card">
                        <div style="font-size:11px; font-weight:bold; color:#3b82f6; margin-bottom:5px;">SEÇİLENLER (<span id="sel-count">0</span>)</div>
                        <div id="sel-list" style="display:flex; flex-wrap:wrap; gap:5px;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Renk ve Kategori</label>
                        <div style="display:flex; gap:10px;">
                            <input type="color" id="adm-color" value="#3b82f6" style="width:50px; height:40px; padding:0; border:none; cursor:pointer;">
                            <select id="adm-cat" class="form-control" onchange="updateColorFromCat()">
                                <option value="custom">Özel Renk</option>
                                <option value="standard" data-color="#ef4444">Standart (Kırmızı)</option>
                                <option value="protocol" data-color="#8b5cf6">Protokol (Mor)</option>
                                <option value="sponsor" data-color="#f59e0b">Sponsor (Sarı)</option>
                                <option value="club" data-color="#3b82f6" selected>Kulüp (Mavi)</option>
                                <option value="staff" data-color="#374151">Görevli (Gri)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label>Not / İsim</label><input type="text" id="adm-note" class="form-control" placeholder="Örn: YBS Kulübü"></div>
                    
                    <div class="form-group" style="display:flex; align-items:center; gap:8px; background:#fef3c7; padding:10px; border-radius:6px; border:1px solid #fde68a;">
                        <input type="checkbox" id="adm-multi" style="width:16px; height:16px;">
                        <label for="adm-multi" style="margin:0; color:#b45309; cursor:pointer;">Bu Koltukları Çoklu Satışa Aç</label>
                    </div>
                </div>

                <div id="admin-view-detail" style="display:none;">
                    <div style="background:#f9fafb; padding:15px; border-radius:8px; border:1px solid #e5e7eb;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <h4 style="margin:0;">Koltuk: <span id="det-id" style="color:var(--c-selected);"></span></h4>
                            <span id="det-status" style="font-size:10px; padding:3px 6px; border-radius:4px; background:#e5e7eb;"></span>
                        </div>
                        
                        <div id="det-single-info" style="font-size:13px; line-height:1.8;">
                            <strong>Kayıtlı Kişi:</strong> <span id="det-name" style="font-weight:bold;"></span><br>
                            <strong>Kategori/Not:</strong> <span id="det-note"></span>
                        </div>
                        
                        <div id="det-multi-list" style="margin-top:15px; display:none;">
                            <strong style="font-size:12px; display:block; margin-bottom:5px;">Bu Koltuktaki Rezervasyonlar:</strong>
                            <div id="multi-items-container"></div>
                        </div>
                    </div>
                    
                    <button type="button" id="btn-archive-club-detail" onclick="archiveClubGroupByNote(this.getAttribute('data-club-note'))" style="display:none; width:100%; margin-top:12px; padding:11px; border-radius:8px; border:2px solid #6366f1; background:#eef2ff; color:#4338ca; font-weight:800; font-size:12px; cursor:pointer;">📦 Kulübü arşive taşı</button>
                    <p id="btn-archive-club-hint" style="display:none; font-size:10px; color:#6b7280; margin:6px 0 0 0; line-height:1.35;">Aynı nottaki <strong>club</strong> kayıtları arşivlenir; koltuklar bireysele açılır.</p>
                    <button id="btn-delete-main" onclick="deleteSeat()" class="btn-danger">KOLTUĞU BOŞALT (SİL)</button>
                    <button id="btn-toggle-multi" onclick="toggleMultiStatus()" class="btn-warning">ÇOKLU SATIŞI KAPAT</button>
                    <button onclick="clearAdminSel()" style="width:100%; margin-top:10px; border:none; background:none; cursor:pointer; color:#666;">Kapat</button>
                </div>
            </div>
            <div class="sidebar-footer"><button id="adm-btn-save" onclick="saveAdminBulk()" class="btn-primary" style="display:none;">KAYDET</button></div>

            <?php elseif ($is_sold_out): ?>
            <div class="sidebar-header"><h3>REZERVASYON DURUMU</h3></div>
            <div class="sidebar-content" style="display:flex; flex-direction:column; justify-content:center; text-align:center; padding-top:40px;">
                <div style="font-size: 70px; line-height: 1; margin-bottom: 15px;">🎫</div>
                <h2 style="color: #ef4444; font-size:22px; font-weight:900; margin: 0 0 10px 0; text-transform:uppercase;">Biletler Tükendi</h2>
                <p style="color: #4b5563; font-size: 14px; line-height: 1.6; margin-bottom: 30px;">
                    Yoğun ilginiz için teşekkür ederiz! Zirvemiz için tüm koltuklarımız dolmuştur. Etkinlikte görüşmek üzere!
                </p>
                <button class="btn-primary" onclick="openTicketModal()" style="margin-bottom:10px;">Aldığım Bileti Sorgula / İndir</button>
                <a href="<?php echo site_url(); ?>" style="display:block; padding:12px; border-radius:8px; border:1px solid #d1d5db; color:#4b5563; text-decoration:none; font-weight:bold; background:#f9fafb;">Ana Sayfaya Dön</a>
            </div>

            <?php else: ?>
            <div class="sidebar-header"><h3>REZERVASYON</h3></div>
            <div class="sidebar-content">
                
                <div id="u-form-step">
                    <div class="ticket-card">
                        <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:800; color:var(--c-selected); margin-bottom:10px;">
                            <span>SEÇİLEN KOLTUK</span><span>🎟️</span>
                        </div>
                        <div id="user-seats-display" style="display:flex; gap:5px;">
                            <span style="font-size:13px; color:#9ca3af;">Haritadan koltuk seçiniz...</span>
                        </div>
                    </div>

                    <div class="form-group"><label>Ad Soyad</label><input type="text" id="u-name" class="form-control" placeholder="Adınız ve Soyadınız"></div>
                    <div class="form-group"><label>E-Posta</label><input type="email" id="u-email" class="form-control" placeholder="Sertifikanız bu maile gelecek"></div>
                    <div class="form-group"><label>Telefon</label><input type="tel" id="u-phone" class="form-control" placeholder="05XX XXX XX XX"></div>

                    <div style="margin-top:20px; margin-bottom:20px; font-size:12px; color:#475569; line-height:1.5;">
                        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; margin-bottom:12px;">
                            <input type="checkbox" id="u-kvkk-aydinlatma" style="margin-top:3px;" required>
                            <span><a href="https://2026.ybszirve.org.tr/kullanim-sartlari/" target="_blank" style="color:#2563eb; text-decoration:underline;">Aydınlatma Metni</a>'ni okudum. Bilet işlemlerimin bu kapsamda yapılmasını kabul ediyorum. <span style="color:#ef4444">*</span></span>
                        </label>

                        <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; background:#f0fdf4; padding:10px; border-radius:6px; border:1px solid #bbf7d0;">
                            <input type="checkbox" id="u-kvkk-sponsor" style="margin-top:3px;">
                            <span style="color:#166534;">🎁 <strong>Staj ve Sürprizler:</strong> Kariyer fırsatları ve sürpriz çekilişler için bilgilerimin Zirve Sponsorları ile paylaşılmasına izin veriyorum.</span>
                        </label>
                    </div>

                    <div id="u-msg" style="text-align:center; font-weight:bold; font-size:13px; margin-bottom: 10px;"></div>
                </div>

                <div id="u-success-step" style="display: none; text-align: center; padding-top: 20px;">
                    <div style="font-size: 60px; line-height: 1; margin-bottom: 10px;">🎉</div>
                    <h3 style="color: #10b981; font-size: 22px; margin: 0 0 10px 0;">Biletin Hazır!</h3>
                    <p style="color: #4b5563; font-size: 14px; margin-bottom: 20px; line-height: 1.6;">
                        Kaydın başarıyla oluşturuldu.<br>
                        <strong>Lütfen bilet görselini indir ve kaybetme.</strong><br>
                        Etkinlik günü salon girişinde ve sertifika yoklamalarında bu biletteki QR kodu okutman gerekecek.
                    </p>
                    
                    <div style="background: #f9fafb; padding: 15px; border: 1px dashed #d1d5db; border-radius: 8px; margin-bottom: 20px;">
                        <span style="font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: bold;">Koltuk Numaran:</span>
                        <div id="success-seat-number" style="font-size: 24px; font-weight: 900; color: #111827; margin-top: 5px;">-</div>
                    </div>
                </div>

            </div>
            
            <div class="sidebar-footer">
                <button id="u-btn-submit" class="btn-primary" disabled>BİLETİ ONAYLA</button>
                <button id="u-btn-download" class="btn-success" style="display:none;">BİLET GÖRSELİNİ İNDİR</button>
            </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<style>
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 99999; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;}
    .modal-content { background: #fff; width: 100%; max-width: 450px; border-radius: 16px; padding: 30px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); position: relative; max-height: 90vh; overflow-y: auto;}
    .modal-close { position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 28px; color: #9ca3af; cursor: pointer; line-height: 1; }
    .modal-close:hover { color: #111827; }
</style>

<div id="ticket-modal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" onclick="closeTicketModal()">&times;</button>
        
        <div id="modal-search-step">
            <h2 style="margin: 0 0 10px; font-size: 20px; font-weight: 800;">Biletinizi Bulun</h2>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">Kayıt olurken kullandığınız E-Posta veya Telefon numarasını yazarak biletinizi görüntüleyebilirsiniz.</p>
            
            <div class="form-group" style="margin-bottom: 15px; text-align: left;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 5px; text-transform: uppercase;">E-Posta veya Telefon</label>
                <input type="text" id="check-contact" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box;" placeholder="Örn: info@mail.com veya 0555...">
            </div>
            
            <div id="check-msg" style="margin-bottom: 15px; font-size: 13px; font-weight: bold; text-align: center;"></div>
            
            <button id="btn-check" style="width: 100%; padding: 14px; background: #111827; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s;" onclick="checkMyTicket()">BİLETİMİ SORGULA</button>
        </div>

        <div id="modal-result-step" style="display:none;">
            <h2 style="margin: 0 0 15px; font-size: 20px; font-weight: 800; color: #10b981; text-align:center;">Biletiniz Bulundu! 🎉</h2>
            
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; text-align:center; margin-bottom:20px;">
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; font-weight: bold; margin-bottom: 5px;">Kayıtlı İsim</div>
                <div id="preview-name" style="font-size: 18px; font-weight: 800; color: #1f2937; margin-bottom: 15px;">-</div>
                
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="font-size: 11px; color: #15803d; text-transform: uppercase; font-weight: bold;">Koltuklar</div>
                    <div id="preview-seats" style="font-size: 16px; font-weight: 900; color: #166534;">-</div>
                </div>

                <div id="preview-qrcode" style="display: flex; padding: 6px; background: #fff; border: 2px dashed #d1d5db; border-radius: 12px; justify-content: center; align-items: center; width: 150px; height: 150px; margin: 0 auto;"></div>
            </div>

            <button id="btn-download-preview" style="width: 100%; padding: 14px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer;">BİLET GÖRSELİNİ İNDİR</button>
        </div>

    </div>
</div>

<!-- ===== ÇOKLU SATIŞ DAĞITIM MODALİ ===== -->
<div id="redistribute-modal" class="modal-overlay">
    <div class="modal-content" style="max-width:660px; max-height:93vh; overflow-y:auto;">
        <button class="modal-close" onclick="document.getElementById('redistribute-modal').style.display='none'">&times;</button>

        <h2 style="margin:0 0 4px; font-size:20px; font-weight:800;">⚖️ Koltuk başına çoklu yükü dengelle</h2>
        <p style="font-size:13px; color:#6b7280; margin:0 0 8px;">Amaç: çoklu rezervasyonları koltuklar arasında yaymak; <strong>bir koltuktaki kişi sayısının üst sınırını</strong> (en kötü koltuk) mümkün olan en düşük seviyeye indirmek. Çoklu koltuk listesi değişmez.</p>
        <p id="redis-load-summary" style="font-size:12px; color:#0e7490; font-weight:600; margin:0 0 18px; padding:8px 10px; background:#ecfeff; border-radius:6px; border:1px solid #a5f3fc;">—</p>

        <!-- Özet kartlar -->
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:18px;">
            <div style="background:#ecfeff; border:1px solid #a5f3fc; border-radius:8px; padding:12px; text-align:center;">
                <div style="font-size:24px; font-weight:900; color:#0891b2;" id="redis-seat-count">-</div>
                <div style="font-size:11px; color:#0891b2; font-weight:700; text-transform:uppercase;">Çoklu Koltuk</div>
            </div>
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; text-align:center;">
                <div style="font-size:24px; font-weight:900; color:#166534;" id="redis-rec-count">-</div>
                <div style="font-size:11px; color:#15803d; font-weight:700; text-transform:uppercase;">Toplam Kayıt</div>
            </div>
            <div style="background:#fefce8; border:1px solid #fef08a; border-radius:8px; padding:12px; text-align:center;">
                <div style="font-size:24px; font-weight:900; color:#854d0e;" id="redis-per-seat">-</div>
                <div style="font-size:11px; color:#92400e; font-weight:700; text-transform:uppercase;">En fazla / koltuk</div>
                <div style="font-size:10px; color:#6b7280; margin-top:2px;">dağıtım sonrası üst sınır</div>
            </div>
        </div>

        <!-- Dağılım tablosu -->
        <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;">Koltuk Bazlı Dağılım</div>
        <div style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; margin-bottom:16px;">
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr 60px; background:#f9fafb; padding:8px 12px; font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb;">
                <span>Koltuk</span><span style="text-align:center;">Şu An</span><span style="text-align:center;">Sonra</span><span style="text-align:center;">Fark</span>
            </div>
            <div id="redis-dist-table" style="max-height:280px; overflow-y:auto;"></div>
        </div>

        <!-- Uyarı -->
        <div style="background:#ecfeff; border:1px solid #a5f3fc; border-radius:8px; padding:12px; margin-bottom:16px; font-size:12px; color:#164e63; display:flex; gap:8px; align-items:flex-start;">
            <span style="font-size:16px; flex-shrink:0;">ℹ️</span>
            <div>Yalnızca <strong id="redis-move-count">?</strong> kayıt farklı bir koltuğa taşınacaktır. Bilet geçerliliği etkilenmez. <strong>Bu işlem geri alınamaz.</strong></div>
        </div>

        <!-- Butonlar -->
        <div style="display:flex; gap:10px;">
            <button id="redis-confirm-btn" onclick="applyRedistribution()" style="flex:1; padding:14px; background:#0891b2; color:#fff; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer;">
                ✅ Onayla ve Uygula
            </button>
            <button onclick="document.getElementById('redistribute-modal').style.display='none'" style="flex:1; padding:14px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer;">
                İptal
            </button>
        </div>
    </div>
</div>

<!-- ===== KOLTUK YENİDEN DÜZENLEME ÖNİZLEME MODALİ ===== -->
<div id="reorganize-modal" class="modal-overlay">
    <div class="modal-content" style="max-width:700px; max-height:93vh; overflow-y:auto;">
        <button class="modal-close" onclick="document.getElementById('reorganize-modal').style.display='none'">&times;</button>

        <h2 style="margin:0 0 4px; font-size:20px; font-weight:800;">🔀 Koltuk Düzeni Önizlemesi</h2>
        <p style="font-size:13px; color:#6b7280; margin:0 0 18px;">Onaylamadan önce önerilen yeni düzeni inceleyin. Değişiklikler geri alınamaz. <strong>Arşivlenmiş kayıtlar</strong> (salon dışına alınmış) bu düzenlemeye dahil edilmez.</p>

        <!-- Özet kartlar -->
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:18px;">
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px; text-align:center;">
                <div style="font-size:24px; font-weight:900; color:#1d4ed8;" id="reorg-std-count">-</div>
                <div style="font-size:11px; color:#3b82f6; font-weight:700; text-transform:uppercase;">Standart</div>
                <div style="font-size:10px; color:#6b7280; margin-top:2px;">Sağ üst köşeye</div>
            </div>
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; text-align:center;">
                <div style="font-size:24px; font-weight:900; color:#166534;" id="reorg-clubs-count">-</div>
                <div style="font-size:11px; color:#15803d; font-weight:700; text-transform:uppercase;">Kulüp Grubu</div>
                <div style="font-size:10px; color:#6b7280; margin-top:2px;">Bir arada gruplanacak</div>
            </div>
            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:12px; text-align:center;">
                <div style="font-size:24px; font-weight:900; color:#374151;" id="reorg-fixed-count">-</div>
                <div style="font-size:11px; color:#6b7280; font-weight:700; text-transform:uppercase;">Değişmeyecek</div>
                <div style="font-size:10px; color:#6b7280; margin-top:2px;">Protokol / Görevli</div>
            </div>
        </div>

        <!-- Harita + Detaylar -->
        <div style="display:grid; grid-template-columns:auto 1fr; gap:18px; align-items:start;">
            <!-- Mini Harita -->
            <div>
                <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Önerilen Düzen</div>
                <div id="reorg-preview-map" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px; display:inline-block;"></div>
                <div style="margin-top:8px; font-size:10px; color:#6b7280; line-height:1.8;">
                    <span style="display:inline-flex; align-items:center; gap:3px; margin-right:8px;"><span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;"></span> Standart</span>
                    <span style="display:inline-flex; align-items:center; gap:3px; margin-right:8px;"><span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;display:inline-block;"></span> Kulüp</span><br>
                    <span style="display:inline-flex; align-items:center; gap:3px; margin-right:8px;"><span style="width:8px;height:8px;border-radius:50%;background:#8b5cf6;display:inline-block;"></span> Protokol</span>
                    <span style="display:inline-flex; align-items:center; gap:3px;"><span style="width:8px;height:8px;border-radius:50%;background:#374151;display:inline-block;"></span> Görevli</span>
                </div>
            </div>

            <!-- Detaylar -->
            <div style="max-height:320px; overflow-y:auto;">
                <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Standart Kayıtlar</div>
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:10px; margin-bottom:14px;">
                    <div style="color:#1d4ed8; font-weight:700; font-size:12px; margin-bottom:4px;">Sağ üst köşe koltuklara taşınacak</div>
                    <div style="color:#3b82f6; font-size:11px;" id="reorg-std-range">-</div>
                </div>

                <div style="font-size:11px; font-weight:700; color:#374151; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">Kulüp Grupları</div>
                <div id="reorg-club-details"></div>
            </div>
        </div>

        <!-- Uyarı -->
        <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:12px; margin-top:16px; font-size:12px; color:#92400e; display:flex; gap:8px; align-items:flex-start;">
            <span style="font-size:16px; flex-shrink:0;">⚠️</span>
            <div><strong>Dikkat:</strong> Bu işlem <strong id="reorg-moves-count">?</strong> kaydın koltuk numarasını değiştirecektir. Standart bilet sahiplerinin koltuk numaraları değişir ancak bilet geçerliliği etkilenmez. Protokol ve Görevli kayıtları bu işlemden etkilenmez. <strong>Bu işlem geri alınamaz.</strong></div>
        </div>

        <!-- Butonlar -->
        <div style="display:flex; gap:10px; margin-top:20px;">
            <button id="reorg-confirm-btn" onclick="applyReorganization()" style="flex:1; padding:14px; background:#1d4ed8; color:#fff; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer; transition:.2s;">
                ✅ Onayla ve Uygula
            </button>
            <button onclick="document.getElementById('reorganize-modal').style.display='none'" style="flex:1; padding:14px; background:#f3f4f6; color:#374151; border:1px solid #d1d5db; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer;">
                İptal
            </button>
        </div>
    </div>
</div>

<div id="overbook-modal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" onclick="closeOverbookModal()">&times;</button>
        <h2 style="margin: 0 0 12px; font-size: 20px; font-weight: 800;">Paylaşımlı (Overbook) Koltuk Nedir?</h2>
        <p style="font-size: 14px; color: #4b5563; line-height: 1.6; margin-bottom: 16px;">
            Bazı koltuklar <strong style="color:#b45309;">paylaşımlı</strong> olarak açılmıştır. Bu koltuklara birden fazla kişi bilet alabilir.
        </p>
        <p style="font-size: 14px; color: #4b5563; line-height: 1.6; margin-bottom: 16px;">
            Salon kapasitesine göre bilet sahipleri <strong>görevliler tarafından belirlenen oturma düzenine</strong> göre yerleştirilir.
        </p>
        <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:8px; padding:12px; display:flex; gap:10px; align-items:flex-start; margin-bottom: 12px;">
            <span style="font-size:20px; flex-shrink:0;">🪑</span>
            <span style="font-size:13px; color:#92400e;">
                Paylaşımlı koltuklar, yoğun ilgi gören oturumlarda kapasiteyi daha verimli kullanmak için tasarlanmıştır.
                Biletiniz her zaman geçerlidir, yalnızca tam koltuk numaranız salonda güncellenebilir.
            </span>
        </div>
        <button onclick="closeOverbookModal()" style="width:100%; margin-top:8px; padding:12px; border-radius:8px; border:none; background:#111827; color:#fff; font-weight:700; cursor:pointer;">
            Anladım
        </button>
    </div>
</div>

<div id="ticket-export-container" style="position: fixed; bottom: -2000px; left: 0; z-index: -1000;">
    <div id="visual-ticket" style="width: 400px; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); font-family: -apple-system, sans-serif; border: 1px solid #e5e7eb; position: relative;">
        <div style="background: #111827; color: #ffffff; padding: 25px 20px; text-align: center; position: relative;">
            <h2 style="margin: 0; font-size: 18px; font-weight: 800; letter-spacing: 1px; line-height: 1.3;">10. ULUSAL YÖNETİM BİLİŞİM SİSTEMLERİ<br>ÖĞRENCİ ZİRVESİ</h2>
            <p style="margin: 8px 0 0; font-size: 13px; color: #9ca3af; text-transform: uppercase; letter-spacing: 2px;">Etkinlik Giriş Bileti</p>
        </div>
        
        <div style="padding: 25px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 15px; margin-bottom: 15px;">
                <div>
                    <span style="display: block; font-size: 11px; color: #6b7280; text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Kayıtlı İsim</span>
                    <div id="ticket-name" style="font-size: 16px; font-weight: 800; color: #1f2937;">-</div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px;">
                <div>
                    <span style="display: block; font-size: 11px; color: #15803d; text-transform: uppercase; font-weight: bold; margin-bottom: 4px;">Koltuklar</span>
                    <div id="ticket-seats" style="font-size: 18px; font-weight: 900; color: #166534;">-</div>
                </div>
                <div style="font-size: 24px;">🪑</div>
            </div>
            
            <div style="text-align: center; margin-top: 25px;">
                <div id="ticket-qrcode" style="display: flex; padding: 6px; background: #fff; border: 2px dashed #d1d5db; border-radius: 12px; justify-content: center; align-items: center; width: 180px; height: 180px; box-sizing: content-box; margin: 0 auto;"></div>
            </div>
        </div>
        
        <div style="background: #f3f4f6; padding: 15px; text-align: center; border-top: 1px dashed #d1d5db;">
            <div style="font-size: 11px; color: #6b7280; font-weight: 600; margin-bottom: 8px;">Lütfen girişte karekodu görevliye gösteriniz.</div>
            <div style="font-size: 11px; color: #374151; font-weight: 800;">📍 Düzce Üniversitesi, Konuralp Yerleşkesi, Merkez/Düzce</div>
        </div>
    </div>
</div>

<script>
// OVERBOOK BİLGİ POPUP
function toggleOverbookInfo(e) {
    e.stopPropagation();
    if (window.innerWidth <= 900) {
        const modal = document.getElementById('overbook-modal');
        if (modal) modal.style.display = 'flex';
        return;
    }
    const popup = document.getElementById('overbook-info-popup');
    if (popup) {
        popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
    }
}
function closeOverbookModal() {
    const modal = document.getElementById('overbook-modal');
    if (modal) modal.style.display = 'none';
}
document.addEventListener('click', function(e) {
    const wrap = document.getElementById('overbook-info-wrap');
    const popup = document.getElementById('overbook-info-popup');
    if (wrap && popup && !wrap.contains(e.target)) {
        popup.style.display = 'none';
    }
});

// ADMIN SİSTEM KAPAT/AÇ AJAX FONKSİYONU
<?php if($is_admin): ?>
function toggleSystemStatus() {
    const btn = document.getElementById('btn-toggle-system');
    btn.innerText = "İşleniyor...";
    btn.disabled = true;

    const adminActionUrl = window.location.pathname + window.location.search;
    let fd = new URLSearchParams();
    fd.append('action', 'ybs_toggle_system_status');
    fd.append('ybs_front_admin_action', '1');

    fetch(adminActionUrl, { method: 'POST', body: fd, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            location.reload();
        } else {
            alert("Hata oluştu!");
            btn.disabled = false;
        }
    }).catch(err => { alert("Bağlantı hatası!"); btn.disabled = false; });
}
<?php endif; ?>

// Modal İşlemleri
function openTicketModal() { 
    document.getElementById('modal-search-step').style.display = 'block';
    document.getElementById('modal-result-step').style.display = 'none';
    document.getElementById('check-msg').innerText = '';
    document.getElementById('check-contact').value = '';
    document.getElementById('btn-check').disabled = false;
    document.getElementById('btn-check').innerText = 'BİLETİMİ SORGULA';
    document.getElementById('ticket-modal').style.display = 'flex'; 
}

function closeTicketModal() { 
    document.getElementById('ticket-modal').style.display = 'none'; 
}

// Bilet Sorgulama İşlemi
function checkMyTicket() {
    const contact = document.getElementById('check-contact').value;
    const msg = document.getElementById('check-msg');
    const btn = document.getElementById('btn-check');

    if(!contact) { msg.style.color = 'red'; msg.innerText = "Lütfen alanları doldurun."; return; }

    btn.disabled = true; btn.innerText = "Sorgulanıyor...";
    msg.style.color = '#3b82f6'; msg.innerText = "Kontrol ediliyor...";

    const fd = new URLSearchParams();
    fd.append('action', 'ybs_check_my_ticket');
    fd.append('contact', contact);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST', body: fd, headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    }).then(r=>r.json()).then(res=>{
        if(res.success) {
            showTicketPreview(res.data.name, res.data.seats, res.data.token);
        } else {
            msg.style.color = '#b91c1c';
            msg.innerText = res.data.message;
            btn.disabled = false; btn.innerText = "BİLETİMİ SORGULA";
        }
    }).catch(err=>{
        msg.style.color = 'red'; msg.innerText = "Sunucu bağlantı hatası.";
        btn.disabled = false; btn.innerText = "BİLETİMİ SORGULA";
    });
}

// Ekranda Bileti Gösterme
function showTicketPreview(name, seatsArray, token) {
    document.getElementById('modal-search-step').style.display = 'none';
    document.getElementById('modal-result-step').style.display = 'block';

    document.getElementById('preview-name').innerText = name;
    document.getElementById('preview-seats').innerText = seatsArray.join(', ');

    const qrContainer = document.getElementById('preview-qrcode');
    qrContainer.innerHTML = ''; 
    
    const verifyUrl = window.location.origin + '/bilet-dogrulama/?token=' + token;

    const qr = new QRious({
        value: verifyUrl,
        size: 150,
        background: '#ffffff',
        foreground: '#1f2937',
        level: 'H'
    });

    const qrImg = document.createElement('img');
    qrImg.src = qr.toDataURL('image/png'); 
    qrImg.style.width = '150px';
    qrImg.style.height = '150px';
    qrContainer.appendChild(qrImg);

    const dlBtn = document.getElementById('btn-download-preview');
    dlBtn.onclick = function() {
        dlBtn.innerText = "İNDİRİLİYOR...";
        dlBtn.disabled = true;
        generateAndDownloadTicket(name, seatsArray, token, false, dlBtn);
    };
}

// Bilet Oluşturma Fonksiyonu
function generateAndDownloadTicket(name, seatsArray, token, isNewBooking = false, callbackBtn = null) {
    document.getElementById('ticket-name').innerText = name;
    document.getElementById('ticket-seats').innerText = seatsArray.join(', ');

    const qrContainer = document.getElementById('ticket-qrcode');
    qrContainer.innerHTML = ''; 
    
    const verifyUrl = window.location.origin + '/bilet-dogrulama/?token=' + token;

    const qr = new QRious({
        value: verifyUrl,
        size: 180,
        background: '#ffffff',
        foreground: '#1f2937',
        level: 'H'
    });

    const qrImg = document.createElement('img');
    qrImg.src = qr.toDataURL('image/png'); 
    qrImg.style.width = '180px';
    qrImg.style.height = '180px';
    qrContainer.appendChild(qrImg);

    const exportContainer = document.getElementById('ticket-export-container');
    const originalStyle = exportContainer.style.cssText;
    exportContainer.style.cssText = 'position: fixed; top: 0; left: -9999px; z-index: -9999; opacity: 1; pointer-events: none; display: block;';

    setTimeout(() => {
        const ticketEl = document.getElementById('visual-ticket');
        
        html2canvas(ticketEl, { 
            scale: 2, 
            backgroundColor: "#ffffff",
            useCORS: true,
            allowTaint: true,
            logging: false
        }).then(canvas => {
            exportContainer.style.cssText = originalStyle;

            const link = document.createElement('a');
            const safeName = name.replace(/\s+/g, '_');
            link.download = 'YBS_Zirve_Bileti_' + safeName + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            if(callbackBtn) {
                callbackBtn.innerText = "BİLET GÖRSELİNİ İNDİR";
                callbackBtn.disabled = false;
            }

            if(isNewBooking) {
                document.getElementById('u-btn-download').innerText = "TEKRAR İNDİR";
                document.getElementById('u-btn-download').disabled = false;
            }
        }).catch(err => {
            exportContainer.style.cssText = originalStyle;
            alert("Bilet oluşturulurken bir hata meydana geldi.");
            if(callbackBtn) {
                callbackBtn.innerText = "BİLET GÖRSELİNİ İNDİR";
                callbackBtn.disabled = false;
            }
            if(isNewBooking) {
                document.getElementById('u-btn-download').innerText = "İNDİRMEYİ DENEYİN";
                document.getElementById('u-btn-download').disabled = false;
            }
        });
    }, 500); 
}

// ---- HARİTA VE ANA SİSTEM İŞLEMLERİ ----
document.addEventListener('DOMContentLoaded', function() {
    
    const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
    const isSoldOut = <?php echo $is_sold_out ? 'true' : 'false'; ?>;
    
    const rawStatusData = <?php echo json_encode($status_data); ?>;
    const statusData = rawStatusData || { booked: [], multi: [] };
    
    let bookedIDs = statusData.booked || []; 
    let multiIDs  = statusData.multi || [];
    
    const rawAdminData = <?php echo !empty($admin_full_data) ? json_encode($admin_full_data) : '{}'; ?>;
    const adminData = rawAdminData || {};
    
    const rawFullData = <?php echo !empty($raw_admin_data) ? json_encode($raw_admin_data) : '{}'; ?>;
    const adminFullData = rawFullData || {};
    
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    const adminActionUrl = window.location.pathname + window.location.search;
    const postAdminAction = (fd) => {
        fd.append('ybs_front_admin_action', '1');
        return fetch(adminActionUrl, {
            method:'POST',
            body: fd,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }).then(r => r.json());
    };

    if (isAdmin) {
        const gtt = document.getElementById('group-tooltip');
        if (gtt) {
            gtt.addEventListener('mouseenter', function() {
                if (window._ybsGroupHideTimer) { clearTimeout(window._ybsGroupHideTimer); window._ybsGroupHideTimer = null; }
            });
            gtt.addEventListener('mouseleave', function() {
                hideGroupHover('');
            });
        }
        const gtArchBtn = document.getElementById('gt-btn-archive');
        if (gtArchBtn) {
            gtArchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const n = this.getAttribute('data-club-note');
                if (n) archiveClubGroupByNote(n);
            });
        }
    }

    const myBookings = JSON.parse(localStorage.getItem('ybs_my_bookings') || '[]');

    // Kullanıcı (ziyaretçi) tarafında overbook (çoklu satış) koltuklarının bir kısmını
    // görsel olarak dolu göstermek için rastgele seçim.
    let multiVisualBookedIDs = new Set();
    if(!isAdmin && !isSoldOut && Array.isArray(multiIDs) && multiIDs.length > 0) {
        const dayKey = new Date().toISOString().slice(0, 10);
        const storageKey = 'ybs_multi_visual_full_v1_' + dayKey + '_' + multiIDs.length;
        let chosen = [];
        try {
            const raw = localStorage.getItem(storageKey);
            chosen = raw ? JSON.parse(raw) : [];
        } catch(e) {
            chosen = [];
        }

        if(!Array.isArray(chosen) || chosen.length === 0) {
            const eligible = multiIDs.filter(id => !myBookings.includes(id));
            // "yarısı" için yukarı yuvarla
            const half = Math.ceil(eligible.length / 2);

            // Fisher-Yates shuffle
            for (let i = eligible.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                const tmp = eligible[i];
                eligible[i] = eligible[j];
                eligible[j] = tmp;
            }
            chosen = eligible.slice(0, half);
            try { localStorage.setItem(storageKey, JSON.stringify(chosen)); } catch(e) {}
        }

        // Sadece mevcut multi koltuklar
        chosen.filter(id => multiIDs.includes(id)).forEach(id => multiVisualBookedIDs.add(id));
    }

    let selectedSeats = [];
    let selectedDetailId = null;

    let generatedTicketName = '';
    let generatedTicketToken = '';
    let generatedTicketSeats = [];

    let sponsorHintShown = false;

    const layout = [
        { row: 'O', r: 4, m: 18, l: 4 }, { row: 'N', r: 7, m: 17, l: 7 },
        { row: 'M', r: 9, m: 16, l: 9 }, { row: 'L', r: 10, m: 15, l: 10 },
        { row: 'K', r: 12, m: 14, l: 11 }, { row: 'J', r: 13, m: 13, l: 12 },
        { row: 'I', r: 13, m: 12, l: 13 }, { row: 'H', r: 13, m: 11, l: 13 },
        { row: 'G', r: 13, m: 10, l: 13 }, { row: 'F', r: 13, m: 9, l: 13 },
        { row: 'E', r: 13, m: 0, l: 13, isGap: true }, 
        { row: 'D', r: 13, m: 8, l: 13 }, { row: 'C', r: 12, m: 7, l: 12 },
        { row: 'B', r: 11, m: 6, l: 11 }, { row: 'A', r: 10, m: 5, l: 10 }
    ];

    const seatMap = document.getElementById('seat-map');
    const mapViewport = document.getElementById('map-viewport');

    function generateSmartLegend() {
        if(!isAdmin) return;
        const container = document.getElementById('smart-legend');
        
        let html = `
            <div class="smart-legend-item"><div class="smart-dot" style="background:#ef4444"></div> Standart</div>
            <div class="smart-legend-item"><div class="smart-dot" style="background:#8b5cf6"></div> Protokol</div>
            <div class="smart-legend-item"><div class="smart-dot" style="background:#f59e0b"></div> Sponsor</div>
            <div class="smart-legend-item"><div class="smart-dot" style="background:#3b82f6"></div> Kulüp</div>
            <div class="smart-legend-item"><div class="smart-dot" style="background:#374151"></div> Görevli</div>
            <div class="smart-legend-item"><div class="smart-dot" style="background:#f59e0b; border-radius:0;"></div> Çoklu Satış</div>
        `;
        if(container) container.innerHTML = html;
    }

    function collectLayoutSeatIds() {
        const ids = [];
        layout.forEach(config => {
            const total = config.r + config.m + config.l;
            for (let i = 0; i < config.l; i++) ids.push(config.row + '-' + (total - i));
            if (!config.isGap) {
                const startMid = total - config.l;
                for (let i = 0; i < config.m; i++) ids.push(config.row + '-' + (startMid - i));
            }
            for (let i = 0; i < config.r; i++) ids.push(config.row + '-' + (config.r - i));
        });
        return ids;
    }

    function renderMap() {
        seatMap.innerHTML = '';
        layout.forEach(config => {
            const rowDiv = document.createElement('div'); rowDiv.className = 'row';
            const total = config.r + config.m + config.l;
            rowDiv.appendChild(createWing(config.l, total, 'left', config.row));
            if(config.isGap) {
                const gap = document.createElement('div'); gap.className = 'gap-large';
                rowDiv.appendChild(gap);
            } else {
                const startMid = total - config.l;
                rowDiv.appendChild(createLabel(config.row));
                rowDiv.appendChild(createWing(config.m, startMid, 'center', config.row));
                rowDiv.appendChild(createLabel(config.row));
            }
            rowDiv.appendChild(createWing(config.r, config.r, 'right', config.row));
            seatMap.appendChild(rowDiv);
        });
        const stageDiv = document.createElement('div'); stageDiv.className = 'stage-box';
        stageDiv.innerHTML = '<div class="stage-visual">SAHNE</div>';
        seatMap.appendChild(stageDiv);

        if(isAdmin) {
            const elStatTotal = document.getElementById('stat-total');
            if(elStatTotal) elStatTotal.innerText = bookedIDs.length;
            
            let totalRegs = 0;
            let bireyselRegs = 0;
            if (adminFullData && adminFullData.reservations) {
                Object.values(adminFullData.reservations).forEach(arr => {
                    totalRegs += arr.length;
                    arr.forEach(row => {
                        if (row && row.category === 'standard') bireyselRegs++;
                    });
                });
            }
            const regEl = document.getElementById('stat-reg-count');
            if(regEl) regEl.innerText = totalRegs;
            const elToplamDet = document.getElementById('stat-detail-toplam');
            if (elToplamDet) elToplamDet.innerText = totalRegs;

            const allSeatIds = collectLayoutSeatIds();
            const seatSet = new Set(allSeatIds);
            const bookedSet = new Set(bookedIDs);
            const multiOnMap = (multiIDs || []).filter(id => seatSet.has(id)).length;
            let bosCount = 0;
            for (let si = 0; si < allSeatIds.length; si++) {
                if (!bookedSet.has(allSeatIds[si])) bosCount++;
            }
            const elBir = document.getElementById('stat-detail-bireysel');
            const elMul = document.getElementById('stat-detail-multi');
            const elBos = document.getElementById('stat-detail-bos');
            if (elBir) elBir.innerText = bireyselRegs;
            if (elMul) elMul.innerText = multiOnMap;
            if (elBos) elBos.innerText = bosCount;
        }
        
        generateSmartLegend();
    }

    function createWing(count, startNum, type, labelText) {
        const wing = document.createElement('div'); wing.className = 'wing ' + type;
        if(type==='left' && labelText) wing.appendChild(createLabel(labelText));

        for(let i=0; i<count; i++) {
            const num = startNum - i;
            const id = (labelText || '') + '-' + num;
            const seat = document.createElement('div');
            seat.className = 'seat'; seat.innerText = num; seat.dataset.id = id; seat.id = 'seat-' + id;

            let isBooked = false;
            let isOverbooked = multiIDs.includes(id);
            const dbHasRecord = bookedIDs.includes(id);

            if (isAdmin) {
                if (dbHasRecord) isBooked = true;
            } else if (isSoldOut) {
                // Satışlar kapalıysa tüm salon dolu göster
                isBooked = true;
            } else {
                if (isOverbooked) {
                    if (myBookings.includes(id)) isBooked = true; 
                    else isBooked = false; 
                } else {
                    if (dbHasRecord) isBooked = true;
                }
            }

            // Ziyaretçi tarafında overbook boş koltukları "dolu gibi" göster
            const shouldVisuallyFull = (!isAdmin && !isBooked && isOverbooked && multiVisualBookedIDs && multiVisualBookedIDs.has(id));

            if(isBooked) {
                seat.classList.add('booked');
                if(isAdmin) {
                    const data = adminData[id];
                    if(data) {
                        const customColor = data.color || '#e74c3c';
                        seat.style.backgroundColor = customColor;
                        seat.style.borderColor = adjustColor(customColor, -20);
                        seat.style.color = '#fff';
                        seat.title = data.user_name ? data.user_name + (data.note ? ' (' + data.note + ')' : '') : (data.note || 'Dolu');
                    }
                } else {
                    seat.classList.add('cat-standard');
                }
            }

            if(shouldVisuallyFull) {
                seat.classList.add('visual-booked');
                seat.title = 'Sınırlı koltuk (görsel dolu)'; // sadece UX için
            }

            if (isAdmin && isOverbooked) {
                seat.classList.add('multi-active');
                if (!isBooked) {
                    seat.classList.add('multi-empty');
                }
            }

            if (isAdmin) {
                seat.addEventListener('mouseenter', function(e) {
                    if (window._ybsGroupHideTimer) { clearTimeout(window._ybsGroupHideTimer); window._ybsGroupHideTimer = null; }
                    showGroupHover(this.dataset.id, e);
                });
                seat.addEventListener('mousemove',  function(e) {
                    const d = adminData[this.dataset.id];
                    if (!d || !d.note) return;
                    const sameNote = Object.keys(adminData).filter(s => adminData[s].note === d.note);
                    const isClubCat = d.category === 'club';
                    if (sameNote.length >= 2 || isClubCat) positionGroupTooltip(e);
                });
                seat.addEventListener('mouseleave', function() {
                    const hid = this.dataset.id;
                    window._ybsGroupHideTimer = setTimeout(function() { hideGroupHover(hid); }, 220);
                });
            }

            wing.appendChild(seat);
        }
        if(type==='right' && labelText) wing.appendChild(createLabel(labelText));
        return wing;
    }

    function createLabel(text) { const d = document.createElement('div'); d.className = 'row-label'; d.innerText = text; return d; }
    function adjustColor(color, amount) { return '#' + color.replace(/^#/, '').replace(/../g, color => ('0'+Math.min(255, Math.max(0, parseInt(color, 16) + amount)).toString(16)).substr(-2)); }

    function positionGroupTooltip(e) {
        const tooltip = document.getElementById('group-tooltip');
        if (!tooltip) return;
        const offset = 18;
        const tw = tooltip.offsetWidth || 220;
        const th = tooltip.offsetHeight || 80;
        let x = e.clientX + offset;
        let y = e.clientY - offset;
        if (x + tw > window.innerWidth - 10) x = e.clientX - tw - offset;
        if (y + th > window.innerHeight - 10) y = e.clientY - th - offset;
        if (y < 10) y = 10;
        tooltip.style.left = x + 'px';
        tooltip.style.top = y + 'px';
    }

    /** Önceki kulüp/grup sarı vurgusunu kaldır (başka koltuğa geçince timer iptal olabiliyor). */
    function clearAllGroupSeatHighlights() {
        const map = document.getElementById('seat-map');
        if (!map) return;
        map.querySelectorAll('.group-highlight, .group-hover-source').forEach(function(el) {
            el.classList.remove('group-highlight');
            el.classList.remove('group-hover-source');
            el.style.backgroundColor = el.dataset.prevBg || '';
            el.style.borderColor = el.dataset.prevBorder || '';
            const sid = el.dataset.id;
            const d = sid ? adminData[sid] : null;
            el.style.color = (d && d.color) ? '#fff' : '';
            delete el.dataset.prevBg;
            delete el.dataset.prevBorder;
        });
        window._ybsTooltipGroupSeats = null;
    }

    function showGroupHover(hoverId, e) {
        clearAllGroupSeatHighlights();

        const hoverData = adminData[hoverId];
        if (!hoverData || !hoverData.note) {
            const tt0 = document.getElementById('group-tooltip');
            const aw0 = document.getElementById('gt-archive-wrap');
            if (tt0) tt0.style.display = 'none';
            if (aw0) aw0.style.display = 'none';
            return;
        }
        const groupNote = hoverData.note;
        const groupSeats = Object.keys(adminData).filter(sid => adminData[sid].note === groupNote);
        const isClubCategory = hoverData.category === 'club';
        if (groupSeats.length < 2 && !isClubCategory) {
            const tt1 = document.getElementById('group-tooltip');
            const aw1 = document.getElementById('gt-archive-wrap');
            if (tt1) tt1.style.display = 'none';
            if (aw1) aw1.style.display = 'none';
            return;
        }

        window._ybsTooltipGroupSeats = groupSeats.slice();

        groupSeats.forEach(sid => {
            const el = document.getElementById('seat-' + sid);
            if (!el) return;
            el.classList.add(sid === hoverId ? 'group-hover-source' : 'group-highlight');
            el.dataset.prevBg = el.style.backgroundColor;
            el.dataset.prevBorder = el.style.borderColor;
            el.style.backgroundColor = sid === hoverId ? '#fde68a' : '#fef08a';
            el.style.borderColor = '#d97706';
            el.style.color = '#78350f';
        });

        const tooltip = document.getElementById('group-tooltip');
        document.getElementById('gt-title-text').textContent = groupNote;
        document.getElementById('gt-count-text').textContent = groupSeats.length + ' koltuk';
        const seatsList = document.getElementById('gt-seats-list');
        seatsList.innerHTML = groupSeats.slice().sort().map(sid =>
            `<span class="gt-seat-tag${sid === hoverId ? ' current' : ''}">${sid}</span>`
        ).join('');

        const arWrap = document.getElementById('gt-archive-wrap');
        const arBtn = document.getElementById('gt-btn-archive');
        if (arWrap && arBtn && isAdmin) {
            if (isClubCategory) {
                arWrap.style.display = 'block';
                arBtn.setAttribute('data-club-note', groupNote);
            } else {
                arWrap.style.display = 'none';
                arBtn.removeAttribute('data-club-note');
            }
        }

        tooltip.style.display = 'block';
        positionGroupTooltip(e);
    }

    function hideGroupHover(hoverId) {
        const tooltip = document.getElementById('group-tooltip');
        const arWrap = document.getElementById('gt-archive-wrap');
        let groupSeats = [];
        if (window._ybsTooltipGroupSeats && window._ybsTooltipGroupSeats.length) {
            groupSeats = window._ybsTooltipGroupSeats;
            window._ybsTooltipGroupSeats = null;
        } else if (hoverId && adminData[hoverId] && adminData[hoverId].note) {
            const gn = adminData[hoverId].note;
            groupSeats = Object.keys(adminData).filter(sid => adminData[sid].note === gn);
        }
        groupSeats.forEach(sid => {
            const el = document.getElementById('seat-' + sid);
            if (!el) return;
            el.classList.remove('group-highlight');
            el.classList.remove('group-hover-source');
            el.style.backgroundColor = el.dataset.prevBg || '';
            el.style.borderColor = el.dataset.prevBorder || '';
            const d = adminData[sid];
            el.style.color = (d && d.color) ? '#fff' : '';
            delete el.dataset.prevBg;
            delete el.dataset.prevBorder;
        });
        if (tooltip) tooltip.style.display = 'none';
        if (arWrap) arWrap.style.display = 'none';
    }

    window.archiveClubGroupByNote = function(clubNote) {
        if (!clubNote) return;
        if (!confirm('Bu kulüp / üniversite grubunun tüm koltuklarını arşive taşıyorsunuz. Fiziksel koltuklar boşalır; kayıtlar silinmez (QR ve istatistikler korunur). Devam?')) return;
        const fd = new URLSearchParams();
        fd.append('action', 'ybs_archive_club_group');
        fd.append('club_note', clubNote);
        postAdminAction(fd).then(function(res) {
            if (res.success) {
                alert(res.data && res.data.message ? res.data.message : 'Tamam.');
                location.reload();
            } else {
                alert(res.data && res.data.message ? res.data.message : 'İşlem başarısız.');
            }
        }).catch(function() { alert('Bağlantı hatası.'); });
    };

    window.updateColorFromCat = function() {
        const select = document.getElementById('adm-cat');
        const color = select.options[select.selectedIndex].getAttribute('data-color');
        if(color) document.getElementById('adm-color').value = color;
    }

    function handleSeatClick(el, id) {
        if (!isAdmin && isSoldOut) return; // SİSTEM KAPALIYSA TIKLAMAYI ENGELLE

        const isBooked = el.classList.contains('booked');
        const isMultiEmpty = el.classList.contains('multi-empty');
        
        if(isAdmin) {
            if(isBooked || isMultiEmpty || multiIDs.includes(id)) {
                showAdminDetail(id);
            } else {
                toggleSelection(el, id);
            }
        } else {
            if(isBooked) return;
            if(selectedSeats.includes(id)) {
                selectedSeats = []; el.classList.remove('selected');
            } else {
                if(selectedSeats.length > 0) {
                    const prev = document.getElementById('seat-' + selectedSeats[0]);
                    if(prev) prev.classList.remove('selected');
                    selectedSeats = [];
                }
                selectedSeats.push(id); el.classList.add('selected');
            }
            updateUserUI();
        }
    }

    function toggleSelection(el, id) {
        document.getElementById('admin-view-detail').style.display = 'none';
        if(selectedSeats.includes(id)) { selectedSeats = selectedSeats.filter(x => x !== id); el.classList.remove('selected'); } 
        else { selectedSeats.push(id); el.classList.add('selected'); }
        updateAdminUI();
    }
    
    function updateAdminUI() {
        const vEmpty = document.getElementById('admin-view-empty'); 
        const vSel = document.getElementById('admin-view-selection'); 
        const btnSave = document.getElementById('adm-btn-save');
        
        if(selectedSeats.length > 0) { 
            vEmpty.style.display = 'none'; 
            vSel.style.display = 'block'; 
            document.getElementById('sel-count').innerText = selectedSeats.length; 
            document.getElementById('sel-list').innerHTML = selectedSeats.map(id => `<span class="seat-tag">${id}</span>`).join(''); 
            btnSave.style.display = 'block'; 
        } else { 
            vEmpty.style.display = 'block'; 
            vSel.style.display = 'none'; 
            btnSave.style.display = 'none'; 
        }
    }

    window.saveAdminBulk = function() {
        if(selectedSeats.length === 0) return;
        const noteRaw = document.getElementById('adm-note');
        const noteTrim = (noteRaw && noteRaw.value) ? String(noteRaw.value).trim() : '';
        if (!noteTrim) {
            alert('Not / İsim alanı boşken kayıt yapılamaz. Lütfen grup veya kişi adını yazın.');
            if (noteRaw) noteRaw.focus();
            return;
        }
        const btn = document.getElementById('adm-btn-save'); 
        btn.disabled = true; 
        btn.innerText = "KAYDEDİLİYOR...";
        
        const fd = new URLSearchParams();
        fd.append('action', 'ybs_admin_manual_bulk_add');
        fd.append('category', document.getElementById('adm-cat').value); 
        fd.append('note', noteTrim);
        fd.append('color', document.getElementById('adm-color').value);
        
        const isMulti = document.getElementById('adm-multi').checked ? '1' : '0';
        fd.append('is_multi', isMulti);
        
        selectedSeats.forEach(id => { fd.append('seats[]', id); });

        postAdminAction(fd)
        .then(res => {
            if(res.success) location.reload();
            else { alert("Hata: " + (res.data && res.data.message ? res.data.message : (typeof res.data === 'string' ? res.data : "Bilinmeyen hata"))); btn.disabled = false; btn.innerText = "KAYDET"; }
        })
        .catch(err => { alert("Sunucuyla bağlantı kurulamadı. Sayfayı yenileyin."); btn.disabled = false; btn.innerText = "KAYDET"; });
    }

    function showAdminDetail(id) {
        window.clearAdminSel(); 
        selectedDetailId = id; 
        
        document.getElementById('admin-view-empty').style.display = 'none'; 
        document.getElementById('admin-view-detail').style.display = 'block';
        document.getElementById('det-id').innerText = id; 
        
        const isMulti = multiIDs.includes(id);
        const statusBadge = document.getElementById('det-status');
        const btnMulti = document.getElementById('btn-toggle-multi');
        
        const singleInfo = document.getElementById('det-single-info');
        const multiList = document.getElementById('det-multi-list');
        const multiContainer = document.getElementById('multi-items-container');
        
        singleInfo.style.display = 'none';
        multiList.style.display = 'none';
        multiContainer.innerHTML = '';
        
        if (isMulti) {
            statusBadge.innerText = "ÇOKLU SATIŞA AÇIK";
            statusBadge.style.background = "#fef3c7";
            statusBadge.style.color = "#b45309";
            btnMulti.style.display = "block";
            btnMulti.innerText = "Çoklu Satışı İptal Et";
            
            if (adminFullData.reservations && adminFullData.reservations[id]) {
                multiList.style.display = 'block';
                const rows = adminFullData.reservations[id];
                
                rows.forEach(row => {
                    const div = document.createElement('div');
                    div.className = 'multi-list-item';
                    div.innerHTML = `
                        <div>
                            <b>${row.user_name || 'İsimsiz'}</b><br>
                            <span style="color:#666">${row.user_email || ''}</span>
                        </div>
                        <button onclick="deleteSingleRecord(${row.id})">Sil</button>
                    `;
                    multiContainer.appendChild(div);
                });
            } else {
                singleInfo.style.display = 'block';
                document.getElementById('det-name').innerText = "Henüz kimse bilet almadı.";
                document.getElementById('det-note').innerText = "Boş";
            }
        } else {
            statusBadge.innerText = "STANDART DOLU";
            statusBadge.style.background = "#fee2e2";
            statusBadge.style.color = "#b91c1c";
            btnMulti.style.display = "block";
            btnMulti.innerText = "Koltuk İçin Çoklu Satışı Aç";
            
            singleInfo.style.display = 'block';
            const data = adminData[id];
            if(data) {
                document.getElementById('det-name').innerText = data.user_name || '-';
                document.getElementById('det-note').innerText = data.note || 'Özel Rezervasyon'; 
            }
        }

        const btnArch = document.getElementById('btn-archive-club-detail');
        const hintArch = document.getElementById('btn-archive-club-hint');
        let showArch = false;
        let archNote = '';
        if (isMulti && adminFullData.reservations && adminFullData.reservations[id]) {
            const rows = adminFullData.reservations[id];
            const allClub = rows.length > 0 && rows.every(function(r) { return r.category === 'club'; });
            if (allClub && rows[0].note) {
                showArch = true;
                archNote = rows[0].note;
            }
        } else if (!isMulti) {
            const data = adminData[id];
            if (data && data.category === 'club' && data.note) {
                showArch = true;
                archNote = data.note;
            }
        }
        if (btnArch) {
            btnArch.style.display = showArch ? 'block' : 'none';
            btnArch.setAttribute('data-club-note', archNote || '');
        }
        if (hintArch) hintArch.style.display = showArch ? 'block' : 'none';
    }

    window.clearAdminSel = function() {
        selectedSeats.forEach(id => { const el = document.getElementById('seat-'+id); if(el) el.classList.remove('selected'); }); 
        selectedSeats = []; 
        updateAdminUI(); 
        
        const vd = document.getElementById('admin-view-detail');
        if(vd) vd.style.display = 'none'; 
        
        const ve = document.getElementById('admin-view-empty');
        if(ve) ve.style.display = 'block';
    }

    window.deleteSeat = function() {
        if(!confirm('Bu koltuğu tamamen boşaltmak istediğinize emin misiniz? (Tüm kayıtlar silinir)')) return;
        const fd = new URLSearchParams(); 
        fd.append('action', 'ybs_admin_delete_seat_all');
        fd.append('seat_id', selectedDetailId);
        postAdminAction(fd).then(res=>{ location.reload(); });
    }
    
    window.deleteSingleRecord = function(dbId) {
        if(!confirm('Bu kişiyi silmek istediğinize emin misiniz?')) return;
        const fd = new URLSearchParams(); 
        fd.append('action', 'ybs_admin_delete_single'); 
        fd.append('id', dbId);
        postAdminAction(fd).then(res=>{ location.reload(); });
    }
    
    window.clearAllSeats = function() {
        const confirm1 = confirm("DİKKAT: Tüm salon tamamen boşaltılacak ve satılan/kayıtlı bütün biletler silinecektir.\n\nBu işlem geri alınamaz! Devam etmek istiyor musunuz?");
        if(!confirm1) return;

        const confirm2 = prompt("İşlemi onaylamak için lütfen büyük harflerle SİL yazın:");
        if(confirm2 !== "SİL") { alert("Güvenlik onayı başarısız. İşlem iptal edildi."); return; }

        const fd = new URLSearchParams();
        fd.append('action', 'ybs_admin_delete_all_seats');
        
        postAdminAction(fd).then(res => {
            if(res.success) { alert("Tüm salon başarıyla boşaltıldı."); location.reload(); } else { alert("Hata oluştu."); }
        }).catch(err => alert("Sunucu ile bağlantı kurulamadı."));
    }

    // ============================================================
    // KOLTUK YENİDEN DÜZENLEME SİSTEMİ
    // ============================================================

    function isArchivedReservationRow(row) {
        if (!row) return true;
        var a = row.archived_from_seat;
        if (a != null && String(a).trim() !== '') return true;
        var sid = row.seat_id;
        if (typeof sid === 'string' && sid.indexOf('ARSV-') === 0) return true;
        return false;
    }

    // Tüm koltuk ID'lerini layout'tan üretir; sağ kanat önce, sonra sol, sonra merkez.
    // NOT: isGap satırı (E) sol+sağ kanatlara sahiptir — onlar da dahil edilir.
    function getSeatsForReorganize() {
        const rightWing = [], leftWing = [], centerWing = [];
        layout.forEach(function(config) {
            const total = config.r + config.m + config.l;
            for (let i = 1; i <= config.r; i++)  rightWing.push(config.row + '-' + i);
            for (let i = 0; i < config.l; i++)   leftWing.push(config.row  + '-' + (total - i));
            // Merkez koltuklar yalnızca gap olmayan satırlarda var
            if (!config.isGap) {
                for (let i = 0; i < config.m; i++) centerWing.push(config.row + '-' + (total - config.l - i));
            }
        });
        return { rightWing, leftWing, centerWing };
    }

    function computeReorganization() {
        if (!adminFullData || !adminFullData.reservations) return null;
        const allRes = adminFullData.reservations;

        // --- 1. Tüm kayıtları seat_id bazında grupla ---
        // fixedGroups: protocol/staff/sponsor → sabit kalır
        // movingGroups: standard/club → yeniden konumlandırılır
        // Her grup: { records:[], category, note, color, isOriginallyMulti }
        const fixedGroups  = {}; // seat_id -> group
        const movingGroups = {}; // seat_id -> group

        Object.values(allRes).forEach(function(rows) {
            rows.forEach(function(row) {
                if (isArchivedReservationRow(row)) return;
                const cat = row.category || 'standard';
                const sid = row.seat_id;
                if (cat === 'protocol' || cat === 'staff' || cat === 'sponsor') {
                    if (!fixedGroups[sid]) fixedGroups[sid] = { records: [], category: cat, note: row.note, color: row.color };
                    fixedGroups[sid].records.push(row);
                } else if (cat === 'standard' || cat === 'club') {
                    if (!movingGroups[sid]) {
                        movingGroups[sid] = {
                            records: [],
                            category: cat,
                            note: row.note || '',
                            color: row.color || (cat === 'club' ? '#3b82f6' : '#ef4444'),
                            isOriginallyMulti: multiIDs.includes(sid)
                        };
                    }
                    movingGroups[sid].records.push(row);
                }
            });
        });

        // Sabit koltuk ID'leri (protokol/görevli/sponsor)
        const fixedIds = new Set(Object.keys(fixedGroups));

        // --- 2. Müsait koltuk havuzları ---
        // Standart havuzu: sağ kanat, arka→ön (sağ üst köşe öncelikli)
        const stdPool = [];
        layout.forEach(function(config) {
            for (var ri = 1; ri <= config.r; ri++) stdPool.push(config.row + '-' + ri);
        });
        const stdAvailable = stdPool.filter(function(id) { return !fixedIds.has(id); });

        // Kulüp havuzu — sürekli fiziksel yol (yılan/serpantın):
        //   1. Sol kanat:  A satırından O satırına (alttan üste)
        //   2. Merkez:     O satırından A satırına (üstten alta)
        //   3. Sağ taşma:  A satırından O satırına (alttan üste)
        // Geçiş noktaları aynı satırda:  O-left son → O-center ilk  |  A-center son → A-right ilk
        const revLayout = layout.slice().reverse(); // A→O sırası
        const clubPool = [];

        // 1. Sol kanat — alttan üste (A→O)
        revLayout.forEach(function(config) {
            var tot = config.r + config.m + config.l;
            for (var li = 0; li < config.l; li++) clubPool.push(config.row + '-' + (tot - li));
        });

        // 2. Merkez — üstten alta (O→A)
        layout.forEach(function(config) {
            if (!config.isGap) {
                var tot = config.r + config.m + config.l;
                for (var ci = 0; ci < config.m; ci++) clubPool.push(config.row + '-' + (tot - config.l - ci));
            }
        });

        // 3. Sağ kanat taşma — alttan üste (A→O)
        revLayout.forEach(function(config) {
            for (var ri2 = 1; ri2 <= config.r; ri2++) clubPool.push(config.row + '-' + ri2);
        });

        // --- 3. Hareketli grupları kategorilere ayır ---
        // Standard gruplar (unique seat bazında)
        const stdGroups  = []; // [sid, group]
        // Club gruplar: note → [{ sid, group }]
        const clubByNote = {};

        Object.entries(movingGroups).forEach(function(entry) {
            const sid = entry[0], grp = entry[1];
            if (grp.category === 'standard') {
                stdGroups.push([sid, grp]);
            } else {
                const note = grp.note || 'Bilinmeyen Kulüp';
                if (!clubByNote[note]) clubByNote[note] = [];
                clubByNote[note].push({ sid: sid, group: grp });
            }
        });

        // Kulüpleri büyükten küçüğe sırala
        const clubNoteEntries = Object.entries(clubByNote).sort(function(a, b) {
            return b[1].length - a[1].length;
        });

        // needed = unique koltuk grubu sayısı (kayıt sayısı değil)
        const totalClubGroups = clubNoteEntries.reduce(function(s, e) { return s + e[1].length; }, 0);
        const needed = stdGroups.length + totalClubGroups;

        // Toplam müsait kontrol (iki havuzun birleşimi, sabit çıkarılır)
        const segs = getSeatsForReorganize();
        const allAvailable = segs.rightWing.concat(segs.leftWing).concat(segs.centerWing)
                              .filter(function(id) { return !fixedIds.has(id); });
        if (needed > allAvailable.length) {
            return { error: 'Yeterli müsait koltuk yok (Gerekli: ' + needed + ' koltuk, Müsait: ' + allAvailable.length + ' koltuk)' };
        }

        // --- 4. Yeni koltuk ataması ---
        // Standartlar → sağ üst köşe (stdAvailable'dan al)
        const stdNewSeat = {}; // old_sid -> new_sid
        var stdCur = 0;
        stdGroups.forEach(function(entry) {
            stdNewSeat[entry[0]] = stdAvailable[stdCur++];
        });

        // Standartların aldığı koltukları işaretle
        const stdUsedSet = new Set(Object.values(stdNewSeat));

        // Kulüpler → ön sıralardan başlayarak (clubPool, sabit ve standart tarafından alınanlar hariç)
        const clubAvailable = clubPool.filter(function(id) {
            return !fixedIds.has(id) && !stdUsedSet.has(id);
        });
        const clubNewSeat = {}; // old_sid -> new_sid
        var clubCur = 0;
        clubNoteEntries.forEach(function(entry) {
            entry[1].forEach(function(item) {
                clubNewSeat[item.sid] = clubAvailable[clubCur++];
            });
        });

        // --- 5. Taşıma listesi ve çoklu satış işaretleme ---
        const moves = [];
        const newMultiSeats = []; // yeni çoklu satış olarak açılacak koltuklar

        function addMoves(groups, newSeatMap) {
            groups.forEach(function(item) {
                var oldSid = item[0] || item.sid;
                var grp    = item[1] || item.group;
                var newSid = newSeatMap[oldSid];
                if (!newSid) return;
                // Eğer orijinal koltuk multi idi veya bu koltuğa >1 kayıt gidiyorsa → multi yap
                var needsMulti = grp.isOriginallyMulti || grp.records.length > 1;
                if (needsMulti && !multiIDs.includes(newSid)) newMultiSeats.push(newSid);
                grp.records.forEach(function(rec) {
                    if (rec.seat_id !== newSid) {
                        moves.push({ id: rec.id, oldSeatId: rec.seat_id, newSeatId: newSid,
                                     userName: rec.user_name, category: grp.category,
                                     note: grp.note, color: grp.color });
                    }
                });
            });
        }

        addMoves(stdGroups, stdNewSeat);
        clubNoteEntries.forEach(function(entry) { addMoves(entry[1], clubNewSeat); });

        // --- 6. Önizleme haritası ---
        const proposedMap = {};
        Object.values(fixedGroups).forEach(function(grp) {
            grp.records.forEach(function(row) {
                proposedMap[row.seat_id] = { color: row.color, note: row.note, category: row.category };
            });
        });
        stdGroups.forEach(function(entry) {
            var ns = stdNewSeat[entry[0]];
            if (ns) proposedMap[ns] = { color: entry[1].color, note: entry[1].records[0].user_name || '', category: 'standard' };
        });
        clubNoteEntries.forEach(function(entry) {
            var note = entry[0];
            entry[1].forEach(function(item) {
                var ns = clubNewSeat[item.sid];
                if (ns) proposedMap[ns] = { color: item.group.color, note: note, category: 'club' };
            });
        });

        // Özet
        const clubsSummary = clubNoteEntries.map(function(entry) {
            var note = entry[0], items = entry[1];
            var totalRecs = items.reduce(function(s, it) { return s + it.group.records.length; }, 0);
            var firstColor = items[0].group.color;
            var newSeats = items.map(function(it) { return clubNewSeat[it.sid]; }).filter(Boolean);
            return { name: note, seatCount: items.length, recordCount: totalRecs, newSeats: newSeats, color: firstColor };
        });

        return {
            moves: moves,
            totalMoves: moves.length,
            newMultiSeats: newMultiSeats,
            standard: {
                seatCount: stdGroups.length,
                recordCount: stdGroups.reduce(function(s, e) { return s + e[1].records.length; }, 0),
                newSeats: stdGroups.map(function(e) { return stdNewSeat[e[0]]; }).filter(Boolean)
            },
            clubs: clubsSummary,
            fixedCount: Object.keys(fixedGroups).length,
            proposedMap: proposedMap
        };
    }

    function renderReorganizePreview(result) {
        document.getElementById('reorg-std-count').textContent   = result.standard.recordCount;
        document.getElementById('reorg-clubs-count').textContent = result.clubs.length;
        document.getElementById('reorg-fixed-count').textContent = result.fixedCount;
        document.getElementById('reorg-moves-count').textContent = result.totalMoves;

        // Standart koltuk aralığı
        var stdSeats = result.standard.newSeats;
        document.getElementById('reorg-std-range').textContent =
            stdSeats.length > 0
                ? stdSeats.slice(0, 6).join(', ') + (stdSeats.length > 6 ? '  …(' + stdSeats.length + ' koltuk)' : '')
                : 'Standart kayıt yok';

        // Kulüp detayları
        var clubDiv = document.getElementById('reorg-club-details');
        if (result.clubs.length === 0) {
            clubDiv.innerHTML = '<div style="font-size:12px;color:#9ca3af;text-align:center;padding:10px;">Kulüp kaydı bulunamadı.</div>';
        } else {
            clubDiv.innerHTML = result.clubs.map(function(club) {
                var range = club.newSeats.slice(0, 4).join(', ') + (club.newSeats.length > 4 ? ' …' : '');
                var hexBg = club.color + '20';
                var multiTag = (club.recordCount > club.seatCount)
                    ? '<span style="font-size:10px;background:#fef3c7;color:#b45309;padding:1px 5px;border-radius:8px;margin-left:4px;">çoklu</span>' : '';
                return '<div style="padding:8px 10px;background:' + hexBg + ';border-left:3px solid ' + club.color + ';border-radius:4px;margin-bottom:6px;">'
                     + '<div style="display:flex;justify-content:space-between;align-items:center;gap:6px;">'
                     + '<strong style="font-size:12px;color:' + club.color + ';">' + club.name + multiTag + '</strong>'
                     + '<span style="font-size:11px;font-weight:700;background:' + hexBg + ';color:' + club.color + ';padding:2px 7px;border-radius:10px;white-space:nowrap;">'
                     + club.recordCount + ' kayıt / ' + club.seatCount + ' koltuk</span>'
                     + '</div>'
                     + '<div style="font-size:11px;color:#6b7280;margin-top:3px;">→ ' + (range || '–') + '</div>'
                     + '</div>';
            }).join('');
        }

        // Mini harita
        var container = document.getElementById('reorg-preview-map');
        container.innerHTML = '';
        var pMap = result.proposedMap;

        layout.forEach(function(config) {
            var rowDiv = document.createElement('div');
            rowDiv.style.cssText = 'display:flex;justify-content:center;align-items:flex-end;margin-bottom:1px;';
            var total = config.r + config.m + config.l;

            var seats = [];
            for (var i = 0; i < config.l; i++) seats.push(config.row + '-' + (total - i));
            if (config.isGap) { seats.push('__gap__'); }
            else { for (var j = 0; j < config.m; j++) seats.push(config.row + '-' + (total - config.l - j)); }
            for (var k = 0; k < config.r; k++) seats.push(config.row + '-' + (config.r - k));

            seats.forEach(function(sid) {
                var dot = document.createElement('div');
                if (sid === '__gap__') {
                    dot.style.cssText = 'width:16px;height:5px;flex-shrink:0;';
                } else {
                    var d = pMap[sid];
                    var color = d ? (d.color || '#94a3b8') : '#e2e8f0';
                    dot.style.cssText = 'width:4px;height:5px;background:' + color + ';border-radius:1px;flex-shrink:0;margin:0 0.5px;';
                    if (d && d.note) dot.title = d.note;
                }
                rowDiv.appendChild(dot);
            });
            container.appendChild(rowDiv);
        });

        var stage = document.createElement('div');
        stage.style.cssText = 'margin-top:6px;text-align:center;font-size:9px;color:#10b981;letter-spacing:2px;border-top:2px solid #10b981;padding-top:3px;font-weight:800;';
        stage.textContent = 'SAHNE';
        container.appendChild(stage);
    }

    window.openReorganizeModal = function() {
        var result = computeReorganization();
        if (!result) { alert('Veri yüklenemedi. Sayfayı yenileyin.'); return; }
        if (result.error) { alert(result.error); return; }
        if (result.totalMoves === 0) { alert('Tüm koltuklar zaten düzenli görünüyor — taşınacak koltuk bulunamadı.'); return; }

        var btn = document.getElementById('reorg-confirm-btn');
        if (btn) { btn.disabled = false; btn.textContent = '✅ Onayla ve Uygula'; }

        renderReorganizePreview(result);
        document.getElementById('reorganize-modal').style.display = 'flex';
    }

    window.applyReorganization = function() {
        var result = computeReorganization();
        if (!result || result.error) { alert(result ? result.error : 'Hata oluştu.'); return; }
        if (result.moves.length === 0) { alert('Taşınacak koltuk bulunamadı.'); return; }

        var btn = document.getElementById('reorg-confirm-btn');
        btn.disabled = true;
        btn.textContent = 'Uygulanıyor…';

        var fd = new URLSearchParams();
        fd.append('action', 'ybs_reorganize_seats');
        // Tüm hareketi tek JSON string olarak gönder (max_input_vars limitini aşmamak için)
        var movesPayload = result.moves.map(function(m) { return { id: m.id, newSeatId: m.newSeatId }; });
        fd.append('moves_json',       JSON.stringify(movesPayload));
        fd.append('newMultiSeats_json', JSON.stringify(result.newMultiSeats));

        postAdminAction(fd).then(function(res) {
            if (res.success) {
                var multiNote = result.newMultiSeats.length > 0
                    ? '\n(' + result.newMultiSeats.length + ' koltuk çoklu satışa açıldı)' : '';
                alert('✅ ' + result.moves.length + ' koltuk başarıyla yeniden düzenlendi!' + multiNote);
                location.reload();
            } else {
                var msg = (res.data && res.data.message) ? res.data.message : 'Bilinmeyen hata';
                alert('Hata: ' + msg);
                btn.disabled = false;
                btn.textContent = '✅ Onayla ve Uygula';
            }
        }).catch(function() {
            alert('Sunucu bağlantı hatası.');
            btn.disabled = false;
            btn.textContent = '✅ Onayla ve Uygula';
        });
    }

    // ============================================================
    // ÇOKLU SATIŞ KAYITLARI DAĞITIM SİSTEMİ
    // ============================================================

    function computeRedistribution() {
        if (!adminFullData || !adminFullData.reservations) return null;
        if (!multiIDs || multiIDs.length === 0) return { error: 'Hiç çoklu satış koltuğu tanımlı değil.' };

        // Her çoklu satış koltuğundaki kayıtları topla
        var allRecords = [];
        var currentDist = {}; // seatId → records[]

        multiIDs.forEach(function(sid) {
            currentDist[sid] = [];
            var rows = adminFullData.reservations[sid];
            if (rows && rows.length > 0) {
                rows.forEach(function(row) {
                    if (isArchivedReservationRow(row)) return;
                    allRecords.push(row);
                    currentDist[sid].push(row);
                });
            }
        });

        var totalSeats   = multiIDs.length;
        var totalRecords = allRecords.length;

        if (totalRecords === 0) return { error: 'Çoklu satış koltuklarında hiç kayıt bulunmuyor.' };

        var maxBefore = 0;
        multiIDs.forEach(function(sid) {
            var n = (currentDist[sid] || []).length;
            if (n > maxBefore) maxBefore = n;
        });

        // Teorik alt sınır: hiçbir koltukta ceil(N/K)’dan fazla olamaz
        var perSeat = Math.ceil(totalRecords / totalSeats);

        // Round-robin: koltuk başına yükü mümkün olan en düşük maksimuma indirir
        var proposedDist = {};
        multiIDs.forEach(function(sid) { proposedDist[sid] = []; });
        for (var ri = 0; ri < allRecords.length; ri++) {
            var targetSeat = multiIDs[ri % totalSeats];
            proposedDist[targetSeat].push(allRecords[ri]);
        }

        // Taşıma listesi (yalnızca gerçekten yer değiştirenler)
        var moves = [];
        multiIDs.forEach(function(sid) {
            proposedDist[sid].forEach(function(rec) {
                if (rec.seat_id !== sid) {
                    moves.push({ id: rec.id, oldSeatId: rec.seat_id, newSeatId: sid });
                }
            });
        });

        return {
            totalSeats   : totalSeats,
            totalRecords : totalRecords,
            perSeat      : perSeat,
            maxBefore    : maxBefore,
            currentDist  : currentDist,
            proposedDist : proposedDist,
            moves        : moves,
            totalMoves   : moves.length
        };
    }

    function renderRedistributePreview(result) {
        document.getElementById('redis-seat-count').textContent = result.totalSeats;
        document.getElementById('redis-rec-count').textContent  = result.totalRecords;
        document.getElementById('redis-per-seat').textContent   = result.perSeat;
        document.getElementById('redis-move-count').textContent = result.totalMoves;
        var sumEl = document.getElementById('redis-load-summary');
        if (sumEl) {
            sumEl.textContent = 'Şu an en yoğun koltuk: ' + result.maxBefore + ' kayıt → dağıtım sonrası hiçbir çoklu koltukta ' + result.perSeat + ' kayıttan fazla olmayacak.';
        }

        var tbody = document.getElementById('redis-dist-table');
        tbody.innerHTML = '';

        multiIDs.forEach(function(sid, idx) {
            var cur  = (result.currentDist[sid]  || []).length;
            var next = (result.proposedDist[sid] || []).length;
            var diff = next - cur;
            var diffStr = diff === 0 ? '–' : (diff > 0 ? '+' + diff : '' + diff);
            var diffColor = diff === 0 ? '#9ca3af' : (diff > 0 ? '#16a34a' : '#dc2626');

            // Satır rengi: fazla dolu olanlar kırmızımsı, boş olanlar yeşilimsi
            var overloaded = cur > result.perSeat + 1;
            var bg = (idx % 2 === 0) ? '#ffffff' : '#f9fafb';
            if (overloaded) bg = '#fef2f2';

            var row = document.createElement('div');
            row.style.cssText = 'display:grid; grid-template-columns:1fr 1fr 1fr 60px; padding:8px 12px; font-size:12px; border-bottom:1px solid #f3f4f6; background:' + bg + '; align-items:center;';
            row.innerHTML =
                '<span style="font-weight:700; color:#374151;">' + sid + '</span>'
              + '<span style="text-align:center; font-weight:600; color:' + (overloaded ? '#b91c1c' : '#374151') + ';">' + cur + ' kayıt</span>'
              + '<span style="text-align:center; font-weight:600; color:#374151;">' + next + ' kayıt</span>'
              + '<span style="text-align:center; font-weight:700; color:' + diffColor + '; font-size:13px;">' + diffStr + '</span>';
            tbody.appendChild(row);
        });
    }

    window.openRedistributeModal = function() {
        var result = computeRedistribution();
        if (!result) { alert('Veri yüklenemedi.'); return; }
        if (result.error) { alert(result.error); return; }

        var btn = document.getElementById('redis-confirm-btn');
        if (btn) { btn.disabled = false; btn.textContent = '✅ Onayla ve Uygula'; }

        renderRedistributePreview(result);
        document.getElementById('redistribute-modal').style.display = 'flex';
    }

    window.applyRedistribution = function() {
        var result = computeRedistribution();
        if (!result || result.error) { alert(result ? result.error : 'Hata oluştu.'); return; }
        if (result.moves.length === 0) { alert('Zaten eşit dağılmış, taşınacak kayıt yok.'); return; }

        var btn = document.getElementById('redis-confirm-btn');
        btn.disabled = true;
        btn.textContent = 'Uygulanıyor…';

        var fd = new URLSearchParams();
        fd.append('action', 'ybs_redistribute_multi');
        fd.append('moves_json', JSON.stringify(result.moves));

        postAdminAction(fd).then(function(res) {
            if (res.success) {
                alert('✅ ' + result.moves.length + ' kayıt başarıyla yeniden dağıtıldı!');
                location.reload();
            } else {
                var msg = (res.data && res.data.message) ? res.data.message : 'Bilinmeyen hata';
                alert('Hata: ' + msg);
                btn.disabled = false;
                btn.textContent = '✅ Onayla ve Uygula';
            }
        }).catch(function() {
            alert('Sunucu bağlantı hatası.');
            btn.disabled = false;
            btn.textContent = '✅ Onayla ve Uygula';
        });
    }

    window.toggleMultiStatus = function() {
        const isMulti = multiIDs.includes(selectedDetailId);
        const action = isMulti ? 'remove_multi' : 'add_multi';
        const confText = isMulti ? "Bu koltuk artık sadece 1 kişiye satılabilecek. Emin misiniz?" : "Bu koltuk aynı anda birden fazla kişiye satılabilecek. Emin misiniz?";
        
        if(!confirm(confText)) return;
        
        const fd = new URLSearchParams(); 
        fd.append('action', 'ybs_admin_toggle_multi');
        fd.append('seat_id', selectedDetailId);
        fd.append('do', action);
        
        postAdminAction(fd).then(res=>{ location.reload(); });
    }

    function updateUserUI() {
        const display = document.getElementById('user-seats-display'); 
        const btn = document.getElementById('u-btn-submit'); 
        const msg = document.getElementById('u-msg'); 
        
        if(selectedSeats.length > 0) { 
            display.innerHTML = selectedSeats.map(id => `<span class="seat-tag">${id}</span>`).join(''); 
            btn.disabled = false; 
            
            if (typeof multiIDs !== 'undefined' && multiIDs.includes(selectedSeats[0])) {
                msg.style.color = '#b45309'; 
                msg.style.backgroundColor = '#fef3c7';
                msg.style.padding = '10px';
                msg.style.borderRadius = '6px';
                msg.style.border = '1px solid #fde68a';
                msg.style.marginTop = '15px';
                msg.innerHTML = '⚠️ <strong>Dikkat:</strong> Seçtiğiniz koltuk "Çoklu Satışa" açıktır. Etkinlik alanında bu bölge paylaşımlı (Overbook) olarak kullanılmaktadır.';
            } else {
                msg.innerHTML = ''; 
                msg.style.padding = '0';
                msg.style.border = 'none';
                msg.style.backgroundColor = 'transparent';
                msg.style.marginTop = '0';
            }

        } 
        else { 
            display.innerHTML = '<span style="font-size:13px; color:#9ca3af;">Haritadan koltuk seçiniz...</span>'; 
            btn.disabled = true; 
            
            msg.innerHTML = ''; 
            msg.style.padding = '0';
            msg.style.border = 'none';
            msg.style.backgroundColor = 'transparent';
            msg.style.marginTop = '0';
        }
    }
    
    if(!isAdmin) {
        const uBtn = document.getElementById('u-btn-submit');
        if(uBtn) {
            uBtn.addEventListener('click', function() {
                const btn = this; 
                const name = document.getElementById('u-name').value; 
                const email = document.getElementById('u-email').value; 
                const phone = document.getElementById('u-phone').value; 
                const kvkkCheck = document.getElementById('u-kvkk-aydinlatma').checked;
                const sponsorChecked = document.getElementById('u-kvkk-sponsor').checked;
                const msg = document.getElementById('u-msg'); 
                
                // Temizle
                msg.innerHTML = '';
                msg.style.padding = '0';
                msg.style.border = 'none';
                msg.style.backgroundColor = 'transparent';
                msg.style.marginTop = '0';
                
                if(!name || !email || !phone) {
                    msg.style.color = '#b91c1c';
                    msg.style.backgroundColor = '#fef2f2';
                    msg.style.border = '1px solid #fee2e2';
                    msg.style.borderRadius = '6px';
                    msg.style.padding = '10px';
                    msg.style.marginTop = '8px';
                    msg.innerHTML = 'Lütfen ad, e-posta ve telefon bilgilerinizi eksiksiz doldurun.';
                    return;
                }
                if(!kvkkCheck) {
                    msg.style.color = '#b91c1c';
                    msg.style.backgroundColor = '#fef2f2';
                    msg.style.border = '1px solid #fee2e2';
                    msg.style.borderRadius = '6px';
                    msg.style.padding = '10px';
                    msg.style.marginTop = '8px';
                    msg.innerHTML = 'Devam edebilmek için <strong>Aydınlatma Metni</strong>ni okuduğunuzu ve onayladığınızı işaretlemeniz gerekiyor.';
                    return;
                }

                if(!sponsorChecked && !sponsorHintShown) {
                    sponsorHintShown = true;
                    msg.style.color = '#166534';
                    msg.style.backgroundColor = '#ecfdf5';
                    msg.style.border = '1px solid #bbf7d0';
                    msg.style.borderRadius = '6px';
                    msg.style.padding = '10px';
                    msg.style.marginTop = '8px';
                    msg.innerHTML = '🎁 <strong>Ek Tavsiye:</strong> İstersen, staj ve kariyer fırsatları için iletişim bilgilerini sponsor firmalarla paylaşabilirsin. Bu alan <strong>zorunlu değildir</strong>, sadece avantaj sağlar. Onaylamadan devam etmek istersen tekrar "BİLETİ ONAYLA" butonuna basman yeterli.';
                    return;
                }
                
                btn.disabled = true; 
                btn.innerText = "KAYDEDİLİYOR...";
                msg.style.color = '#3b82f6';
                msg.innerHTML = "Lütfen bekleyin, biletiniz onaylanıyor...";
                msg.style.padding = "0"; msg.style.border = "none"; msg.style.backgroundColor = "transparent";
                
                const fd = new URLSearchParams(); 
                fd.append('action', 'ybs_make_reservation'); 
                fd.append('name', name); 
                fd.append('email', email); 
                fd.append('phone', phone); 
                fd.append('sponsor_izin', sponsorChecked ? '1' : '0'); 
                selectedSeats.forEach(id => fd.append('seats[]', id));
                
                fetch(ajaxUrl, { method:'POST', body:fd, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
                .then(r=>r.json()).then(res=>{
                    if(res.success) { 
                        selectedSeats.forEach(sid => { if(!myBookings.includes(sid)) myBookings.push(sid); });
                        localStorage.setItem('ybs_my_bookings', JSON.stringify(myBookings));
                        
                        generatedTicketName = name;
                        generatedTicketSeats = selectedSeats;
                        generatedTicketToken = res.data && res.data.token ? res.data.token : 'temp_token';

                        document.getElementById('u-form-step').style.display = 'none';
                        document.getElementById('u-success-step').style.display = 'block';
                        
                        document.getElementById('success-seat-number').innerText = selectedSeats.join(', ');

                        document.getElementById('u-btn-submit').style.display = 'none';
                        document.getElementById('u-btn-download').style.display = 'block';
                    }
                    else { 
                        msg.style.color = '#b91c1c'; 
                        msg.innerText = res.data && res.data.message ? res.data.message : 'Kayıt Başarısız'; 
                        btn.disabled = false; 
                        btn.innerText = "BİLETİ ONAYLA"; 
                    }
                }).catch(err => {
                    msg.style.color = '#b91c1c'; 
                    msg.innerText = 'Sunucuya bağlanılamadı.'; 
                    btn.disabled = false; 
                    btn.innerText = "BİLETİ ONAYLA"; 
                });
            });
        }

        const dlBtn = document.getElementById('u-btn-download');
        if(dlBtn) {
            dlBtn.addEventListener('click', function() {
                this.disabled = true;
                this.innerText = "İNDİRİLİYOR...";
                generateAndDownloadTicket(generatedTicketName, generatedTicketSeats, generatedTicketToken, true, this); 
            });
        }
    }

    let scale = 0.8, pX = 0, pY = 0;
    let isDragging = false, isPinching = false;
    let startX = 0, startY = 0, initialDistance = 0, initialScale = scale;

    function updateT() { seatMap.style.transform = `translate(${pX}px, ${pY}px) scale(${scale})`; }
    function fit() {
        const cw = mapViewport.offsetWidth;
        const mw = seatMap.scrollWidth;
        const isMobile = window.innerWidth <= 900;
        let baseScale = (cw / mw) * (isMobile ? 1.1 : 0.9);
        if (baseScale > 1.2) baseScale = 1.2;
        if (baseScale < 0.45) baseScale = 0.45;
        scale = baseScale;
        pX = 0;
        pY = isMobile ? -40 : 0;
        updateT();
    }

    mapViewport.addEventListener('touchstart', e => {
        if (e.touches.length === 1) { isDragging = true; isPinching = false; startX = e.touches[0].clientX - pX; startY = e.touches[0].clientY - pY; } 
        else if (e.touches.length === 2) { isDragging = false; isPinching = true; initialDistance = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); initialScale = scale; }
    }, { passive: false });

    window.addEventListener('touchmove', e => {
        if (isDragging && e.touches.length === 1) { e.preventDefault(); pX = e.touches[0].clientX - startX; pY = e.touches[0].clientY - startY; updateT(); } 
        else if (isPinching && e.touches.length === 2) { e.preventDefault(); const dist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); const delta = dist - initialDistance; const newScale = initialScale + (delta * 0.005); if (newScale > 0.2 && newScale < 3) { scale = newScale; updateT(); } }
    }, { passive: false });

    window.addEventListener('touchend', e => { isDragging = false; isPinching = false; });

    let touchStartTime = 0;
    seatMap.addEventListener('touchstart', (e) => { touchStartTime = Date.now(); }, {passive: false});
    seatMap.addEventListener('touchend', (e) => {
        const touchDuration = Date.now() - touchStartTime;
        if (touchDuration < 200 && !isDragging && !isPinching) {
            const target = e.target.closest('.seat');
            if (target) { e.preventDefault(); handleSeatClick(target, target.dataset.id); }
        }
    });

    mapViewport.addEventListener('mousedown', e => { if(e.button===0){ isDragging=true; startX=e.clientX-pX; startY=e.clientY-pY; mapViewport.style.cursor='grabbing'; } });
    window.addEventListener('mousemove', e => { if(isDragging && !isPinching){ pX=e.clientX-startX; pY=e.clientY-startY; updateT(); } });
    window.addEventListener('mouseup', () => { isDragging=false; mapViewport.style.cursor='grab'; });
    seatMap.addEventListener('click', (e) => { const target = e.target.closest('.seat'); if (target) handleSeatClick(target, target.dataset.id); });

    mapViewport.addEventListener('wheel', e => { e.preventDefault(); const d = e.deltaY > 0 ? -0.1 : 0.1; if(scale+d > 0.2 && scale+d < 3) { scale+=d; updateT(); } });
    document.getElementById('zi').onclick = () => { scale+=0.2; updateT(); };
    document.getElementById('zo').onclick = () => { if(scale>0.3)scale-=0.2; updateT(); };
    document.getElementById('zr').onclick = fit;

    renderMap();
    window.addEventListener('resize', fit);
    setTimeout(fit, 100);
});
</script>

<?php get_footer(); ?>
