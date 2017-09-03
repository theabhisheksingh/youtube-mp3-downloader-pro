<?php

if (!(isset($_INIT_) && $_INIT_)) {
    exit;
}

?>
<h2>DMCA Panel</h2>
<div class="adminPage">
    <div class="admin-form">
        <label>
            Add video/channel/word to DMCA records:&nbsp;<span style="color:#fd5c63">*</span><br>
            <input type="text" placeholder="Enter YouTube video/channel/user link (or) bad word"
                   id="dmcaValue" spellcheck="false" style="width:100%">
        </label>

        <div style="height:10px"></div>
        <label>
            Record type:<br>
            <label><input type="radio" name="decordType" style="top:2px;margin-left:0"
                          value="video" checked>&nbsp;Video</label>
            <label><input type="radio" name="decordType" style="top:2px" value="channel">&nbsp;Channel</label>
            <label><input type="radio" name="decordType" style="top:2px" value="word">&nbsp;Word</label>
        </label>

        <div style="height:10px"></div>
        <label>
            Record notes:<br>
            <textarea placeholder="Add some notes for this record"
                      id="dmcaNote" spellcheck="false" style="width:100%;height:50px"></textarea>
        </label>
        <input type="submit" value="Add to DMCA records" id="addDMCA">
        <span id="ajaxResult">
            <span class="loading"><img src="<?php echo uniqueX\General::siteLink('assets/images/fb-load.gif') ?>"
                                       alt="Processing..." title="Processing..."></span>
            <span class="success"><span class="okTXT">DMCA record successfully added</span><span
                    class="fa fa-close"></span></span>
            <span class="failed"><span class="noTXT"></span><span class="fa fa-close"></span></span>
        </span>
        <br>
        <strong>DMCA records:</strong>
        <table id="dmcaRecords">
            <thead>
            <tr>
                <th style="width:100px">Record Type</th>
                <th style="width:200px">Record Value</th>
                <th style="width:200px">Record Time</th>
                <th>Record note</th>
                <th style="width:60px">Actions</th>
            </tr>
            </thead>
            <tbody id="dmcaList">
            <?php
            // Get DMCA records
            if (is_array($dmcaRecords = uniqueX\DMCA::getDMCA())) {
                foreach (array_reverse($dmcaRecords) as $dmca) {
                    $note = htmlspecialchars($dmca[3]);
                    // Print data
                    echo '<tr data-dmca="' . $dmca[1] . '">';
                    echo '<td>' . ucfirst($dmca[0]) . '</td>';
                    echo '<td>' . $dmca[1] . '</td>';
                    echo '<td>' . date("M jS, Y h:i:s a", $dmca[2]) . '</td>';
                    echo "<td title=\"{$note}\"><div class=\"rNote\">{$note}</div></td>";
                    echo '<td><ul><li><span class="fa fa-close del-dmca"></span></li></ul></td>';
                    echo '</tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
