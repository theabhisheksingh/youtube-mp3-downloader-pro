<?php
if (isset($socialLinks) && is_array($socialLinks) && isset($socialLinks['facebook'])) { ?>
<script type="text/javascript">var _popwnd =1 </script>
    <div id="fb-root"></div>
    <script>(function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
    <?php
}
?>
<div id="header">
    <div id="menu">
        <div class="menuLayer">
            <?php
            if (isset($config['top-menu']) && is_string($config['top-menu']) && strlen(trim($config['top-menu'])) > 0) {
                echo "<ul id=\"menuList\">{$config['top-menu']}</ul>";
            }

            if (isset($socialLinks) && is_array($socialLinks) && count($socialLinks) > 0) {
                echo '<ul id="menuSocial">';
                foreach ($socialLinks as $id => $value) {
                    if (strlen($value = trim($value)) > 0) {
                        switch ($id) {
                            case 'facebook':
                                echo '<li><a rel="nofollow" target="_blank" href="http://www.facebook.com/' .
                                    $value . '" title="Like ' . $config['site-name'] .
                                    ' on FaceBook">Facebook</a></li>';
                                break;
                            case 'twitter':
                                echo '<li><a rel="nofollow" target="_blank" href="http://www.twitter.com/' .
                                    $value . '" title="Follow ' . $config['site-name'] .
                                    ' on Twitter">Twitter</a></li>';
                                break;
                            case 'google+':
                                echo '<li><a rel="nofollow" target="_blank" href="http://plus.google.com/' .
                                    $value . '" title="Follow ' . $config['site-name'] .
                                    ' on Google+">Google+</a></li>';
                                break;
                        }
                    }
                }
                echo '</ul>';
            }
            ?>
            <ul id="menuMobile">
                <?php
                if (isset($config['top-menu']) &&
                    is_string($config['top-menu']) &&
                    strlen(trim($config['top-menu'])) > 0
                ) {
                    echo $config['top-menu'];
                }
                if (isset($socialLinks) && is_array($socialLinks) && count($socialLinks) > 0) {
                    echo '<li>Social media<ul>';
                    foreach ($socialLinks as $id => $value) {
                        switch ($id) {
                            case 'facebook':
                                echo '<li><a rel="nofollow" target="_blank" href="http://www.facebook.com/' .
                                    $value . '" title="Like ' . $config['site-name'] .
                                    ' on FaceBook">Facebook</a></li>';
                                break;
                            case 'twitter':
                                echo '<li><a rel="nofollow" target="_blank" href="http://www.twitter.com/' .
                                    $value . '" title="Follow ' . $config['site-name'] .
                                    ' on Twitter">Twitter</a></li>';
                                break;
                            case 'google+':
                                echo '<li><a rel="nofollow" target="_blank" href="http://plus.google.com/' .
                                    $value . '" title="Follow ' . $config['site-name'] .
                                    ' on Google+">Google+</a></li>';
                                break;
                        }
                    }
                    echo '</ul></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <div id="logo"><a data-special-link="@Charts" href="<?php echo uniqueX\General::siteLink('@Charts'); ?>"
                      title="<?php echo $config['site-name']; ?>"><?php echo $config['site-name']; ?></a></div>
    <div class="searchParent">
        <div id="search">
            <div class="s-packup"></div>
            <input type="text" id="search_in" spellcheck="false" placeholder="Enter youtube video link or search term">

            <div id="search_go"><span class="fa fa-search"></span></div>
        </div>
    </div>
    <div style="position:relative;height:50px"></div>
</div>