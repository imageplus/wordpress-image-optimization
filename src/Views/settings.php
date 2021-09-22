<div class="wrap">
    <section class="ipio-title">
        <h1 class="wp-heading">Image Optimization</h1>
    </section>

    <div id="poststuff">
        <div id="post-body" class="ipio-page">
            <section class="" id="post-body-content">
                <?php if (!session_id()){ session_start(); } ?>
                <?php if(isset($_SESSION['ipio_messages'])): ?>
                    <?php foreach($_SESSION['ipio_messages'] as $message): ?>
                        <div class='notice notice-<?= $message['type']; ?> is-dismissible'>
                            <p><?= $message['message']; ?></p>
                        </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['ipio_messages']); ?>
                <?php endif; ?>

                <form action="<?= esc_url(admin_url('admin-post.php')); ?>" method="post" id="ipio_optimize_all" >
                    <input type="hidden" name="action" value="ipio_optimize_all">
                    <input type="hidden" name="ipio_optimize_all_nonce" value="<?= wp_create_nonce( 'ipio_optimize_all'); ?>" />

                    <p class="submit">
                        <button type="submit" class="button button-primary">Optimize All Images</button>
                    </p>
                </form>
            </section>
        </div>
    </div>
</div>
