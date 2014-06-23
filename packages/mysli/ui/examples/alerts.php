<div class="section">
    <div class="container spaced default <?php echo get_alt(); ?>">
        <div class="alert">
            <div class="message warn">
                This is a Warning!
                <a href="#" class="close"></a>
            </div>
            <div class="message error">
                I'm an Error.
                <a href="#" class="close"></a>
            </div>
            <div class="message info">
                Hello, I'd like to tell you something...
                <a href="#" class="close"></a>
            </div>
            <div class="message success">
                Yay! Successfully done!
                <a href="#" class="close"></a>
            </div>
        </div>
        <div class="alert" style="margin-top: 20px;">
            <div class="message info">
                <i class="fa spinner fa-spinner fa-spin"></i>
                I'm doing something! Please wait...
            </div>
        </div>
    </div>
    <p><?php echo alt_link(); ?></p>
</div>
