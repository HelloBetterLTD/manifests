<div id="$HolderID" class="field<% if $extraClass %> $extraClass<% end_if %>">
    <% if $Title %><label class="left" for="$ID">$Title</label><% end_if %>
    <style>
        {$Styles}
    </style>
    <div class="middleColumn" id="$ID">
        $ExcelData
    </div>

    <p>
        <a href="{$DownloadLink()}" class="ss-ui-button">Download Excel Sheet</a>
    </p>


    <% if $RightTitle %><label class="right" for="$ID">$RightTitle</label><% end_if %>
    <% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
    <% if $Description %><span class="description">$Description</span><% end_if %>
</div>
