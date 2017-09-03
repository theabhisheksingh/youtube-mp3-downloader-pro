<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

// Get configuration
$config = uniqueX\Backend::getValue('config');
$config = is_array($config) ? $config : array();

// Get new configuration from http request
$newConf = isset($_POST) && is_array($_POST) && count($_POST) > 0 ? $_POST : false;
if (is_array($newConf) && count($newConf) == 2 && isset($newConf['user'], $newConf['pass'])) {
    $newConf = false;
}

// Check new config
if (is_array($newConf)) {
    uniqueX\Backend::setConfig($newConf);
    // Redirect user
    header('Location: ' . uniqueX\General::siteLink('$admin?update=' . md5(uniqueX\Backend::installKey())));
    exit;
}

if (isset($_GET['update']) && $_GET['update'] == md5(uniqueX\Backend::installKey())) {
    echo '<div class="adminCredits">Site configuration successfully updated</div>';
}

?>
<h2>Site configuration</h2>
<!-- Product purchase details -->
<div class="abox">
    <div class="abox-c">
        <div class="abHead">Product purchase details<span class="fa fa-book"></span></div>
        <div class="abBody">
            <div class="conf-help">
                <div class="chelp">
                    <strong>Purchase mail & code:</strong><br>

                    <div class="chTXT">Enter your valid purchase details. You can find your product purchase details
                        on your mail inbox received from uniqueX.
                    </div>
                </div>
            </div>
            <div class="col col-2">
                <div class="col-c">
                    <label>
                        Purchase mail:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['purchase-mail']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="purchase-mail"
                               placeholder="Enter your valid product purchase email address">
                    </label>
                </div>
                <div class="col-c col-l">
                    <label>
                        Purchase code:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['purchase-code']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="purchase-code"
                               placeholder="Enter your valid product purchase license code">
                    </label>
                </div>
                <div class="col-s"></div>
            </div>
        </div>
    </div>
</div>
<!-- API keys -->
<div class="abox">
    <div class="abox-c">
        <div class="abHead">API keys<span class="fa fa-book"></span></div>
        <div class="abBody">
            <div class="conf-help">
                <div class="chelp">
                    <strong>YouTube API key:</strong><br>

                    <div class="chTXT">
                        Enter valid YouTube API key. Please use your own youtube API key. Create a new YouTube API key
                        for this product. Don't use others YouTube API keys. Because if Key limit exceeds your site
                        will display "Videos not available" error and it effect your search engine rank. So, please use
                        your own YouTube API key. It's 100% free.<br>Please watch this video <a
                            target="_blank" href="https://youtu.be/XKx7MMTduVk">How to get YouTube API key?</a>
                        <br>
                        <strong>NOTE:</strong>&nbsp;If you have more than one YouTube API key. You can use multiple API
                        keys by separating with comma. (Ex: API_KEY_ONE, API_KEY_TWO, API_KEY_THREE)
                    </div>
                </div>
                <div class="chelp">
                    <strong>AddThis share key:</strong><br>

                    <div class="chTXT">
                        This feature allows users to share there favorite videos with there friends with more than 350+
                        social media networks. AddThis share key is 100% free.<br>Please watch this video <a
                            target="_blank" href="https://youtu.be/EIn850Z7gpE">How to get AddThis share key?</a>
                    </div>
                </div>
            </div>
            <div class="col col-2">
                <div class="col-c">
                    <label>
                        YouTube API key:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['youtube-key']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="youtube-key"
                               placeholder="Enter your YouTube API key">
                    </label>
                </div>
                <div class="col-c col-l">
                    <label>
                        AddThis share key:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['addthis-key']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="addthis-key"
                               placeholder="Enter your AddThis share key">
                    </label>
                </div>
                <div class="col-s"></div>
            </div>
        </div>
    </div>
</div>
<!-- Site details -->
<div class="abox">
    <div class="abox-c">
        <div class="abHead">Site details<span class="fa fa-book"></span></div>
        <div class="abBody">
            <div class="conf-help">
                <div class="chelp">
                    <strong>Site address:</strong><br>

                    <div class="chTXT">
                        Enter your your website address here. For example&nbsp;<strong>example.com</strong>
                    </div>
                </div>
                <div class="chelp">
                    <strong>Site name:</strong><br>

                    <div class="chTXT">
                        Sitename displays on your website header with big letters
                    </div>
                </div>
                <div class="chelp">
                    <strong>Site slogan:</strong><br>

                    <div class="chTXT">
                        Your site slogan will diplay on site footer with small letters
                    </div>
                </div>
                <div class="chelp">
                    <strong>Site contact email:</strong><br>

                    <div class="chTXT">
                        This email address required for website contact page. Your website contact form messages will
                        send to this email address. So, please enter your valid email address here
                    </div>
                </div>
            </div>
            <div class="col col-4">
                <div class="col-c">
                    <label>
                        Site address:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['site-addr']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="site-addr"
                               placeholder="example.com">
                    </label>
                </div>
                <div class="col-c">
                    <label>
                        Site name:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['site-name']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="site-name"
                               placeholder="Enter your site name">
                    </label>
                </div>
                <div class="col-c">
                    <label>
                        Site slogan:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['site-slogan']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="site-slogan"
                               placeholder="Enter your site slogan">
                    </label>
                </div>
                <div class="col-c col-l">
                    <label>
                        Site contact email:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['site-contact']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="site-contact"
                               placeholder="Enter your site contact page email address">
                    </label>
                </div>
                <div class="col-s"></div>
            </div>
        </div>
    </div>
</div>
<!-- Advanced features -->
<div class="abox">
    <div class="abox-c">
        <div class="abHead">Advanced features<span class="fa fa-book"></span></div>
        <div class="abBody">
            <div class="conf-help">
                <div class="chelp">
                    <strong>Auto detect country:</strong><br>

                    <div class="chTXT">
                        Auto detect visitor country by IP address and display top videos.
                    </div>
                </div>
                <div class="chelp">
                    <strong>Default top charts country:</strong><br>

                    <div class="chTXT">
                        Choose your default top chart country. On your website display top 10 popular videos from this
                        country as default. Visitor can choose there own country.
                    </div>
                </div>
                <div class="chelp">
                    <strong>Instant mode:</strong><br>

                    <div class="chTXT">
                        Display video results via AJAX without reloading total page
                    </div>
                </div>
                <div class="chelp">
                    <strong>Safe search:</strong><br>

                    <div class="chTXT">
                        Enable safe search (Family safe/Chaild safe) on your website. Strict means only display family
                        safe results. None means it displays any type of video maybe contain some porn also.
                    </div>
                </div>
                <div class="chelp">
                    <strong>Max videos per page:</strong><br>

                    <div class="chTXT">
                        How many video results load per one time. Default is 10. You can set value from 1 - 50.
                    </div>
                </div>

                <div class="chelp">
                    <strong>Latest searches length:</strong><br>

                    <div class="chTXT">
                        How many latest search results can show on side panel. Default value is 20. You can set from 1 -
                        100.
                    </div>
                </div>
            </div>
            <div class="col col-3">
                <div class="col-c">
                    <label>
                        Auto detect country:<br>
                        <select data-conf="detect-country">
                            <option value="yes">Enable</option>
                            <?php
                            if (@$config['detect-country'] == 'no') {
                                echo '<option value="no" selected>Disable</option>';
                            } else {
                                echo '<option value="no">Disable</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="col-c">
                    <label>
                        Default top charts country:<br>
                        <select data-conf="default-chart">
                            <?php
                            $chartCode = isset($config['default-chart']) && strlen($config['default-chart']) > 0
                                ? $config['default-chart'] : 'US';
                            // Get charts
                            $charts = uniqueX\YouTube::charts();
                            $totalCountries = 0;
                            if (is_array($charts)) {
                                $chartCountries = array_keys($charts['list']);
                                foreach ($charts['countries'] as $continent => $group) {
                                    echo '<optgroup label="' . $continent . '">';
                                    foreach ($group as $code => $name) {
                                        if (in_array(strtolower($code), $chartCountries)) {
                                            echo '<option value="' . $code . '"' .
                                                ($chartCode == $code ? ' selected' : null)
                                                . '>' . $name . '</option>';
                                            $totalCountries++;
                                        }
                                    }
                                    echo '</optgroup>';
                                }
                            }
                            if ($totalCountries == 0) {
                                echo '<option value="">';
                                echo 'Please provide purchase details and submit this form ';
                                echo 'to see the list of available countries';
                                echo '</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="col-c col-l">
                    <label>
                        Instant mode:<br>
                        <select data-conf="instant-mode">
                            <option value="yes">Enable</option>
                            <?php
                            if (@$config['instant-mode'] == 'no') {
                                echo '<option value="no" selected>Disable</option>';
                            } else {
                                echo '<option value="no">Disable</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="col-s"></div>
            </div>
            <div class="col col-3">
                <div class="col-c">
                    <label>
                        Safe search:<br>
                        <select data-conf="safe-search">
                            <option value="none"<?php
                            echo @$config['safe-search'] == 'none' ? ' selected' : null; ?>>
                                None
                            </option>
                            <option
                                value="moderate"
                                <?php
                                echo @$config['safe-search'] == 'moderate' ? ' selected' : null; ?>>
                                Moderate
                            </option>
                            <option
                                value="Strict"
                                <?php
                                echo @$config['safe-search'] == 'Strict' ? ' selected' : null; ?>>
                                strict
                            </option>
                        </select>
                    </label>
                </div>
                <div class="col-c">
                    <label>
                        Max videos per page:<br>
                        <input type="text" value="<?php
                        $maxVideos = @intval($config['max-videos']);
                        echo $maxVideos >= 1 && $maxVideos <= 50 ? $maxVideos : 10;
                        ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="max-videos"
                               placeholder="Max videos per page">
                    </label>
                </div>
                <div class="col-c col-l">
                    <label>
                        Latest searches length:<br>
                        <input type="text" value="<?php
                        $latestSearches = @intval($config['latest-searches']);
                        echo $latestSearches >= 1 && $latestSearches <= 100 ? $latestSearches : 20;
                        ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="latest-searches"
                               placeholder="Latest searches list length">
                    </label>
                </div>
                <div class="col-s"></div>
            </div>
        </div>
    </div>
</div>
<!-- Media process -->
<div class="abox">
    <div class="abox-c">
        <div class="abHead">Media process<span class="fa fa-book"></span></div>
        <div class="abBody">
            <div class="conf-help">
                <div class="chelp">
                    <strong>Direct download:</strong><br>

                    <div class="chTXT">
                        Allow users to download video files directly from YouTube server. It saves lot of your server
                        bandwidth.
                    </div>
                </div>
                <div class="chelp">
                    <strong>YouTube connection:</strong><br>

                    <div class="chTXT">
                        Some times YouTube will block IPv6 addresses as temporary. So, at that time you can use IPv4
                        address of your server. So, If you think your IPv6 address is blocked by YouTube. Choose "Use
                        IPV4 only" value.
                    </div>
                </div>
                <div class="chelp">
                    <strong>MP3 Converter:</strong><br>

                    <div class="chTXT">
                        If your hosting provider does ot accept execute softwares (like FFmpeg) on your server. Please
                        use "FLV to MP3 Converter (64kbps)" value. It converts YouTube FLV video to MP3 audio at 64kbps
                        audio quality without any softwares. If you don't see "FLV to MP3 Converter (64kbps)" option in
                        list. You can enable that plugin from plugins panel. See torubleshooter panel to install FFmpeg
                        engine on product.
                    </div>
                </div>
                <div class="chelp">
                    <strong>Curl Engine:</strong><br>

                    <div class="chTXT">
                        If you are using FFmpeg for MP3 conversion process. You must need CURL on your server. If you
                        don't have CURL on your server. Choose "product" value. Product use portable CURL engine. See
                        torubleshooter panel to install CURL engine on product.
                    </div>
                </div>
            </div>
            <div class="col col-4">
                <div class="col-c">
                    <label>
                        Direct download:<br>
                        <select data-conf="direct-download">
                            <option value="no">Disable</option>
                            <?php
                            if (@$config['direct-download'] == 'yes') {
                                echo '<option value="yes" selected>Enable</option>';
                            } else {
                                echo '<option value="yes">Enable</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="col-c">
                    <label>
                        YouTube Connection:<br>
                        <select data-conf="force-ipv4">
                            <option value="no" <?php echo @$config['force-ipv4'] == 'no' ? 'selected' : null; ?>>
                                Default
                            </option>
                            <option value="yes" <?php echo @$config['force-ipv4'] == 'yes' ? 'selected' : null; ?>>Use
                                IPV4 only
                            </option>
                        </select>
                    </label>
                </div>
                <div class="col-c">
                    <label>
                        MP3 Converter:<br>
                        <select data-conf="mp3-converter">
                            <option value="ffmpeg">FFmpeg</option>
                            <?php
                            if (uniqueX\Backend::checkPlugin('flv2mp3')) {
                                if (@$config['mp3-converter'] == 'flv2mp3') {
                                    echo '<option value="flv2mp3" selected>FLV to MP3 Converter (64kbps)</option>';
                                } else {
                                    echo '<option value="flv2mp3">FLV to MP3 Converter (64kbps)</option>';
                                }
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="col-c col-l">
                    <label>
                        Curl engine:<br>
                        <select data-conf="curl-engine">
                            <option value="product">Product</option>
                            <option
                                value="system" <?php echo @$config['curl-engine'] == 'system' ? 'selected' : null; ?>>
                                System
                            </option>
                        </select>
                    </label>
                </div>
                <div class="col-s"></div>
            </div>
        </div>
    </div>
</div>
<!-- Social accounts -->
<div class="abox">
    <div class="abox-c">
        <div class="abHead">Social accounts<span class="fa fa-book"></span></div>
        <div class="abBody">
            <div class="conf-help">
                <div class="chelp">
                    <strong>Facebook:</strong><br>

                    <div class="chTXT">
                        Enter your facebook FAN page ID here (not total URL just page ID only). Example:
                        1234567890, myFbFanPage
                    </div>
                </div>
                <div class="chelp">
                    <strong>Twitter:</strong><br>

                    <div class="chTXT">
                        Enter your website twitter account ID (not total URL just account ID only). Example:
                        myWebsiteAtTwiiter
                    </div>
                </div>
                <div class="chelp">
                    <strong>Google+:</strong><br>

                    <div class="chTXT">
                        Enter your Google+ page ID (not total URL just page ID only). Example:
                        1234567890, +googlePlusPage
                    </div>
                </div>
            </div>
            <div class="col col-3">
                <div class="col-c">
                    <label>
                        Facebook:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['facebook']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="facebook"
                               placeholder="Facebook FAN page ID">
                    </label>
                </div>
                <div class="col-c">
                    <label>
                        Twitter:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['twitter']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="twitter"
                               placeholder="Twitter account ID">
                    </label>
                </div>
                <div class="col-c col-l">
                    <label>
                        Google+:<br>
                        <input type="text"
                               value="<?php echo @htmlspecialchars($config['gplus']); ?>"
                               autocomplete="off"
                               spellcheck="false"
                               data-conf="gplus"
                               placeholder="Google+ page ID">
                    </label>
                </div>
                <div class="col-s"></div>
            </div>
        </div>
    </div>
</div>
<!-- SEO section -->
<div class="cLayers">
    <ul class="clTabs">
        <li data-clid="home" class="active">Home Page SEO</li>
        <li data-clid="chart">Charts Page SEO</li>
        <li data-clid="search">Search Page SEO</li>
        <li data-clid="video">Video Page SEO</li>
        <li data-clid="robots-txt">Robots.txt file content</li>
    </ul>
    <div class="clContent">
        <div data-clid="home" class="active">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Home page details for SEO<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Home page title:</strong><br>

                                <div class="chTXT">Set page title for home page</div>
                            </div>
                            <div class="chelp">
                                <strong>Home page keywords:</strong><br>

                                <div class="chTXT">Set page keywords for home page</div>
                            </div>
                            <div class="chelp">
                                <strong>Home page description:</strong><br>

                                <div class="chTXT">Set page description for home page</div>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    Home page title:<br>
                                    <input type="text"
                                           value="<?php echo @htmlspecialchars($config['home-page-title']); ?>"
                                           autocomplete="off"
                                           spellcheck="false"
                                           data-conf="home-page-title"
                                           placeholder="Home page title">
                                </label>
                            </div>
                            <div class="col-c">
                                <label>
                                    Home page keywords:<br>
                        <textarea placeholder="Home page keywords" data-conf="home-page-keywords"
                            ><?php echo @htmlspecialchars($config['home-page-keywords']); ?></textarea>
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Home page description:<br>
                        <textarea placeholder="Home page description" data-conf="home-page-description"
                            ><?php echo @htmlspecialchars($config['home-page-description']); ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div data-clid="chart">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Charts page details for SEO<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Charts page title:</strong><br>

                                <div class="chTXT">
                                    Set page title for charts page (Support dynamic values)<br>
                                    Example: Top Charts - {{chart-name}} | {{site-name}}
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Charts page keywords:</strong><br>

                                <div class="chTXT">
                                    Set page keywords for charts page (Support dynamic values)<br>
                                    Example: {{chart-name}}, {{chart-code}}, top charts, video, music, mp3, mp4,
                                    download
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Charts page description:</strong><br>

                                <div class="chTXT">
                                    Set page description for charts page (Support dynamic values)<br>
                                    Example: {{chart-name}} top charts. Watch and download top videos from
                                    {{chart-name}}
                                </div>
                            </div>
                            <div class="chelp">
                                <table>
                                    <thead>
                                    <tr>
                                        <th style="width:25%">Dynamic value</th>
                                        <th>Result</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>{{site-name}}</td>
                                        <td>Website name</td>
                                    </tr>
                                    <tr>
                                        <td>{{chart-code}}</td>
                                        <td>Top charts country ISO 3166-1 alpha-2 code (Example: US, CA, GB)</td>
                                    </tr>
                                    <tr>
                                        <td>{{chart-name}}</td>
                                        <td>Top charts country full name (Example: United States, Canada, United
                                            Kingdom)
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    Chart page title:<br>
                                    <input type="text"
                                           value="<?php echo @htmlspecialchars($config['chart-page-title']); ?>"
                                           autocomplete="off"
                                           spellcheck="false"
                                           data-conf="chart-page-title"
                                           placeholder="Charts page title">
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Chart page keywords:<br>
                        <textarea placeholder="Charts page keywords" data-conf="chart-page-keywords"
                            ><?php
                            echo @htmlspecialchars($config['chart-page-keywords']);
                            ?></textarea>
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Chart page description:<br>
                        <textarea placeholder="Charts page description" data-conf="chart-page-description"
                            ><?php
                            echo @htmlspecialchars($config['chart-page-description']);
                            ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div data-clid="search">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Search page details for SEO<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Search page title:</strong><br>

                                <div class="chTXT">
                                    Set page title for search page (Support dynamic values)<br>
                                    Example: {{search}} videos | {{site-name}}
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Search page keywords:</strong><br>

                                <div class="chTXT">
                                    Set page keywords for search page (Support dynamic values)<br>
                                    Example: {{search}}, video, music, mp3, mp4, download
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Search page description:</strong><br>

                                <div class="chTXT">
                                    Set page description for search page (Support dynamic values)<br>
                                    Example: Download {{search}} videos and mp3 music with {{site-name}}
                                </div>
                            </div>
                            <div class="chelp">
                                <table>
                                    <thead>
                                    <tr>
                                        <th style="width:25%">Dynamic value</th>
                                        <th>Result</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>{{search}}</td>
                                        <td>Full search term text (Example: Taylor Swift blank space)</td>
                                    </tr>
                                    <tr>
                                        <td>{{site-name}}</td>
                                        <td>Website name</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    Search page title:<br>
                                    <input type="text"
                                           value="<?php echo @htmlspecialchars($config['search-page-title']); ?>"
                                           autocomplete="off"
                                           spellcheck="false"
                                           data-conf="search-page-title"
                                           placeholder="Search page title">
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Search page keywords:<br>
                        <textarea placeholder="Search page keywords" data-conf="search-page-keywords"
                            ><?php echo @htmlspecialchars($config['search-page-keywords']); ?></textarea>
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Search page description:<br>
                        <textarea placeholder="Search page description" data-conf="search-page-description"
                            ><?php echo @htmlspecialchars($config['search-page-description']); ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div data-clid="video">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Video page details for SEO<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Video page title:</strong><br>

                                <div class="chTXT">
                                    Set page title for video page (Support dynamic values)<br>
                                    Example: {{video-title}}.mp3 ({{mp3-size}}) - Download MP3 | {{site-name}}
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Video page keywords:</strong><br>

                                <div class="chTXT">
                                    Set page keywords for video page (Support dynamic values)<br>
                                    Example: {{video-title}}, {{video-owner}}, 320kbps, HD. download, mp3, mp4, flv,
                                    webm, 3gp, m4a
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Video page description:</strong><br>

                                <div class="chTXT">
                                    Set page description for video page (Support dynamic values)<br>
                                    Example: {{video-description}}
                                </div>
                            </div>
                            <div class="chelp">
                                <table>
                                    <thead>
                                    <tr>
                                        <th style="width:25%">Dynamic value</th>
                                        <th>Result</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>{{video-id}}</td>
                                        <td>YouTube video ID</td>
                                    </tr>
                                    <tr>
                                        <td>{{video-title}}</td>
                                        <td>Video title</td>
                                    </tr>
                                    <tr>
                                        <td>{{video-description}}</td>
                                        <td>Video description</td>
                                    </tr>
                                    <tr>
                                        <td>{{video-duration}}</td>
                                        <td>Video duration in seconds (Ex: 234)</td>
                                    </tr>
                                    <tr>
                                        <td>{{video-time}}</td>
                                        <td>Video duration in human friendly time (Ex: 1:04:45)</td>
                                    </tr>
                                    <tr>
                                        <td>{{video-date}}</td>
                                        <td>Video upload date (Ex: Jan 1st, 2013)</td>
                                    </tr>
                                    <tr>
                                        <td>{{video-owner}}</td>
                                        <td>Video upload owner name (Ex: TaylorSwiftVEVO)</td>
                                    </tr>
                                    <tr>
                                        <td>{{mp3-size}}</td>
                                        <td>Video mp3 download size (Ex: 4.69MB)</td>
                                    </tr>
                                    <tr>
                                        <td>{{site-name}}</td>
                                        <td>Website name</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    Video page title:<br>
                                    <input type="text"
                                           value="<?php echo @htmlspecialchars($config['video-page-title']); ?>"
                                           autocomplete="off"
                                           spellcheck="false"
                                           data-conf="video-page-title"
                                           placeholder="Video page title">
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Video page keywords:<br>
                        <textarea placeholder="Video page keywords" data-conf="video-page-keywords"
                            ><?php echo @htmlspecialchars($config['video-page-keywords']); ?></textarea>
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Video page description:<br>
                        <textarea placeholder="Video page description" data-conf="video-page-description"
                            ><?php echo @htmlspecialchars($config['video-page-description']); ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
        <div data-clid="robots-txt">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Robots.txt for search engines<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>robots.txt content:</strong><br>

                                <div class="chTXT">
                                    Enter your robots.txt content here. It creates dynamic robots.txt file for search
                                    engines
                                </div>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    robots.txt content:<br>
                        <textarea placeholder="Enter robots.txt content here"
                                  data-conf="robots-txt"
                                  spellcheck="false"
                                  style="min-height:200px"
                            ><?php
                            echo @htmlspecialchars($config['robots-txt']);
                            ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>
<!-- Advertising & Tracking codes -->
<div class="cLayers">
    <ul class="clTabs">
        <li data-clid="top-menu" class="active">Top menu links</li>
        <li data-clid="advt-videos">Advt on video results</li>
        <li data-clid="advt-sidepanel">Advt on side panel</li>
        <li data-clid="tracking-code">Tracking codes</li>
    </ul>
    <div class="clContent">
        <div data-clid="top-menu" class="active">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Top menu links HTML code<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Top menu links:</strong><br>

                                <div class="chTXT">
                                    Enter your top menu links HTML code here. Example:<br>
                                    <?php
                                    $one = '<li><a href="http://example.com/page-one.html">Page #1</a></li>';
                                    $two = '<li><a target="_blank" href="http://example.com/page-two.html">Page #2</a></li>';
                                    echo htmlspecialchars($one) . '<br>' . htmlspecialchars($two);
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    Top menu links:<br>
                        <textarea placeholder="Enter your top menu links HTML code"
                                  data-conf="top-menu"
                                  spellcheck="false"
                                  style="min-height:200px"
                            ><?php
                            echo @htmlspecialchars($config['top-menu']);
                            ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div data-clid="advt-videos">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Advertising on video results<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Above video results (728x90):</strong><br>

                                <div class="chTXT">
                                    Display advertisement above video search results. Recommeneded advt dimensions
                                    (728x90, 468x60)
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Below video results (728x90):</strong><br>

                                <div class="chTXT">
                                    Display advertisement below video search results. Recommeneded advt dimensions
                                    (728x90, 468x60)
                                </div>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    Above video results (728x90):<br>
                                    <textarea placeholder="Set advt above video search results"
                                              data-conf="advt-above-results"
                                              spellcheck="false"
                                        ><?php
                                        echo @htmlspecialchars($config['advt-above-results']);
                                        ?></textarea>
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Below video results (728x90):<br>
                                    <textarea placeholder="Set advt below video search results"
                                              data-conf="advt-below-results"
                                              spellcheck="false"
                                        ><?php
                                        echo @htmlspecialchars($config['advt-below-results']);
                                        ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div data-clid="advt-sidepanel">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Advertising on side panel<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Side panel top advt (300x250, 300x300, 300x600):</strong><br>

                                <div class="chTXT">
                                    Display advertisement in side panel at top position. Recommeneded advt dimensions
                                    (300x250, 300x300, 300x600)
                                </div>
                            </div>
                            <div class="chelp">
                                <strong>Side panel bottom advt (300x250, 300x300, 300x600):</strong><br>

                                <div class="chTXT">
                                    Display advertisement in side panel at bottom position. Recommeneded advt dimensions
                                    (300x250, 300x300, 300x600)
                                </div>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c">
                                <label>
                                    Side panel top advt (300x250, 300x300, 300x600):<br>
                                    <textarea placeholder="Set advt on side panel at top position"
                                              data-conf="advt-side-panel-top"
                                              spellcheck="false"
                                        ><?php
                                        echo @htmlspecialchars($config['advt-side-panel-top']);
                                        ?></textarea>
                                </label>
                            </div>
                            <div class="col-c col-l">
                                <label>
                                    Side panel bottom advt (300x250, 300x300, 300x600):<br>
                                    <textarea placeholder="Set advt on side panel at bottom position"
                                              data-conf="advt-side-panel-bottom"
                                              spellcheck="false"
                                        ><?php
                                        echo @htmlspecialchars($config['advt-side-panel-bottom']);
                                        ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
        <div data-clid="tracking-code">
            <div class="abox">
                <div class="abox-c">
                    <div class="abHead">Website tracking codes<span class="fa fa-book"></span></div>
                    <div class="abBody">
                        <div class="conf-help">
                            <div class="chelp">
                                <strong>Tracking codes:</strong><br>

                                <div class="chTXT">
                                    Set HTML tracking codes here. (Ex: Google analytics tracking code)
                                </div>
                            </div>
                        </div>
                        <div class="col col-1">
                            <div class="col-c col-l">
                                <label>
                                    Tracking codes:<br>
                                    <textarea placeholder="Set tracking codes (Ex: Google analytics)"
                                              data-conf="tracking-code"
                                              spellcheck="false"
                                        ><?php
                                        echo @htmlspecialchars($config['tracking-code']);
                                        ?></textarea>
                                </label>
                            </div>
                            <div class="col-s"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>
<div style="clear:both"></div>
<div class="adminPanel"><input type="button" id="updateConf" value="Update configuration"></div>
