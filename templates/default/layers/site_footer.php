<div id="footer">

    <div style="position:relative;height:35px"></div>

    <div class="footLayer">

        <div>

            <div class="fl-block">

                <a href="#"><h3><?php echo $config['site-name']; ?></h3></a>



                <div class="slogan"><?php echo $config['site-slogan']; ?></div>

            </div>

            <div class="fl-block">

                <ul>

                    <li><a href="<?php echo uniqueX\General::siteLink('terms'); ?>">Terms of Service</a></li>

                    <li><a href="<?php echo uniqueX\General::siteLink('privacy'); ?>">Privacy Policy</a></li>

                    <li><a href="<?php echo uniqueX\General::siteLink('contact'); ?>">Contact Us</a></li>

                </ul>

                <br>

                <span class="copy-right">Copyright &copy; <?php echo date('Y') . " " . $config['site-name']; ?></span>

            </div>

            <div class="fl-block">

                <?php

                if (isset($socialLinks) && is_array($socialLinks) && count($socialLinks) > 0) {

                    echo '<ul class="social-menu">';

                    foreach ($socialLinks as $id => $value) {

                        if (strlen($value = trim($value)) > 0) {

                            switch ($id) {

                                case 'facebook':

                                    echo '<li><a rel="nofollow" target="_blank" href="http://www.facebook.com/' .

                                        $value . '" title="Like ' . $config['site-name'] .

                                        ' on FaceBook"><span class="fa fa-facebook"></span></a></li>';

                                    break;

                                case 'twitter':

                                    echo '<li><a rel="nofollow" target="_blank" href="http://www.twitter.com/' .

                                        $value . '" title="Follow ' . $config['site-name'] .

                                        ' on Twitter"><span class="fa fa-twitter"></span></a></li>';

                                    break;

                                case 'google+':

                                    echo '<li><a rel="nofollow" target="_blank" href="http://plus.google.com/' .

                                        $value . '" title="Follow ' . $config['site-name'] .

                                        ' on Google+"><span class="fa fa-google-plus"></span></a></li>';

                                    break;

                            }

                        }

                    }

                    echo '</ul>';

                }

                ?>

            </div>

        </div>

        <div style="position:relative;clear:both"></div>

    </div>

    <div style="position:relative;height:35px"></div>

</div>

<?php

// Attach body contents

$attachBody = uniqueX\Backend::getValue('attachBody');

if (is_array($attachBody)) {

    foreach ($attachBody as $content) {

        echo $content;

    }

}

// Set tracking code

echo isset($config['tracking-code']) ? $config['tracking-code'] : null;

?>