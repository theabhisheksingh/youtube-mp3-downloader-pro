<?php

echo
isset($config['advt-side-panel-top']) &&
is_string($config['advt-side-panel-top']) &&
strlen($config['advt-side-panel-top']) > 0
    ? "<div class='area-300' style='margin-bottom:20px'>{$config['advt-side-panel-top']}</div>" : null;

if (isset($addThis) && strlen($addThis) > 0) { ?>
    <div id="sharePage">
        <span class="fa fa-share-alt"></span>&nbsp;<span class="share-text">Share this page</span>
    </div>
    <?php
}

// Get latest searches
if (isset($ogTags) && is_array($ogTags) && isset($ogTags['query']) &&
    !preg_match('/\.([a-z0-9]{1-4})\s*$/i', $ogTags['query'])
) {
    $latestSearches = uniqueX\General::latestSearches($ogTags['page'] == 'search' ? $ogTags['query'] : null);
} else {
    $latestSearches = uniqueX\General::latestSearches(null);
}
?>
<div id="rsParent"></div>
<div id="lsParent">
    <?php
    // Display latest searches
    if (isset($latestSearches) && is_array($latestSearches) && count($latestSearches) > 0) { ?>
        <div id="latestSearches" class="contentBox">
            <div class="cbHead">Latest Searches</div>
            <div class="cbBody">
                <ul>
                    <?php
                    foreach ($latestSearches as $search) {
                        $searchTerm = htmlspecialchars(ucfirst($search));
                        echo "<li><a href=\"";
                        echo uniqueX\General::siteLink(uniqueX\General::safeString($search));
                        echo "\" title=\"{$searchTerm}\">{$searchTerm}</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    <?php }
    ?>
</div>
<?php
if (isset($socialLinks) &&
    is_array($socialLinks) &&
    isset($socialLinks['facebook']) &&
    strlen(trim($socialLinks['facebook'])) > 0
) { ?>
    <div class="socialBox">
        <div class="sbChild1">
            <div class="sbChild2">
                <div class="fb-page" data-href="https://www.facebook.com/<?php echo $socialLinks['facebook']; ?>"
                     data-small-header="false"
                     data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"
                     data-show-posts="false">
                    <div class="fb-xfbml-parse-ignore">
                        <blockquote cite="https://www.facebook.com/<?php echo $socialLinks['facebook']; ?>">
                            <a href="https://www.facebook.com/<?php echo $socialLinks['facebook']; ?>"
                               rel="nofollow">Facebook</a>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php }

echo isset($config['advt-side-panel-bottom'])
    ? "<div class='area-300'>{$config['advt-side-panel-bottom']}</div>" : null;

?>

