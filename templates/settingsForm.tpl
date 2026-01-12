{**
 * templates/settingsForm.tpl
 * Tampilan untuk halaman pengaturan LoA.
 *}
<script>
    $(function() {ldelim}
        $('#loaSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
    {rdelim});
</script>

<div class="pkp_page_content">
    <form class="pkp_form" id="loaSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
        {csrf}
        {include file="controllers/notification/inPlaceNotification.tpl" notificationId="loaSettingsFormNotification"}
        
        {fbvFormArea id="loaGenreSettings"}
            {fbvFormSection title="plugins.generic.loa.settings.genre"}
                <p>{translate key="plugins.generic.loa.settings.genre.description"}</p>
                {if $genreOptions}
                    {* translate=false untuk mengatasi translate key ##...## *}
                    {fbvElement type="select" id="loaGenreId" from=$genreOptions selected=$loaGenreId label="plugins.generic.loa.settings.genre.label" translate=false}
                {else}
                    <p>{translate key="plugins.generic.loa.settings.genre.noGenres"}</p>
                {/if}
            {/fbvFormSection}
        {/fbvFormArea}
        
        {fbvFormArea id="loaTemplateSettings"}
            {fbvFormSection title="plugins.generic.loa.settings.template"}
                <p>{translate key="plugins.generic.loa.settings.template.description"}</p>
                {* Hanya Body yang bisa diedit dan menggunakan editor teks kaya *}
                {* {fbvElement type="textarea" id="loaHeader" value=$loaHeader rich=true label="plugins.generic.loa.settings.header"} *}
                {fbvElement type="textarea" id="loaBody" value=$loaBody rich=true label="plugins.generic.loa.settings.body"}
                {*{fbvElement type="textarea" id="loaFooter" value=$loaFooter rich=false label="plugins.generic.loa.settings.footer"} *}
            {/fbvFormSection}
        {/fbvFormArea}
        
        {fbvFormButtons}
    </form>
</div>        