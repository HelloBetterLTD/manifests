<div id="reportadmin-cms-content" class="cms-content center cms-tabset $BaseCSSClasses" data-layout-type="border" data-pjax-fragment="Content">
    <% with $EditForm %>
    <div class="cms-content-header north">

            <div class="cms-content-header-info">
                <% include BackLink_Button %>
                <% with $Controller %>
                    <% include CMSBreadcrumbs %>
                <% end_with %>
            </div>
    </div>

    <div class="cms-content-fields center ui-widget-content" data-layout-type="border">

        $forTemplate

    </div>

    <% end_with %>

</div>