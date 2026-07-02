<div class="wrap">
    <h1 class="wp-heading-inline">Departmanlar</h1>
    <a href="#" id="btn-open-dept-modal" class="page-title-action">Yeni Departman Ekle</a>
    <hr class="wp-header-end">

    <div class="ybs-dept-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top:20px;">
        <?php
        $depts = get_posts(['post_type'=>'departman', 'numberposts'=>-1]);
        if($depts): foreach($depts as $dept):
            // Üye sayısını bul
            $users = get_users(['meta_key' => 'ybs_department_id', 'meta_value' => $dept->ID]);
            $count = count($users);
        ?>
        <div class="dept-card" style="background:#fff; padding:25px; border-radius:10px; border:1px solid #e2e8f0; position:relative;">
            <h2 style="margin-top:0; color:#002855; font-size:1.3em;"><?php echo $dept->post_title; ?></h2>
            <div style="font-size:0.9em; color:#64748b; margin-bottom:15px; height:40px; overflow:hidden;">
                <?php echo wp_trim_words($dept->post_content, 10); ?>
            </div>
            
            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:15px;">
                <span style="font-weight:bold; color:#00B5AD; font-size:1.1em;"><?php echo $count; ?> Üye</span>
                <div>
                    <a href="<?php echo get_edit_post_link($dept->ID); ?>" class="button">Düzenle</a>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
            <p>Hiç departman yok.</p>
        <?php endif; ?>
    </div>
</div>