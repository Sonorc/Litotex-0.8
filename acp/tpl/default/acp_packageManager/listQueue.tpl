{include file=$HEADER}
<h1>{#acp_packageManager_updateManager#}</h1>
<h3>{#acp_packageManager_queueList#}</h3>
<p>{sprintf(#acp_packageManager_queueCount#, count($installQueue))}</p>
<p>{if $error}<span style="color:red">{#acp_packageManager_queueProblemsDetected#}</span>{else}{#acp_packageManager_queueNoProblems#}{/if}</p>
<p>
<form onsubmit="if(!checkbox_allItemsSelected(document.getElementsByName('update[]'))) {literal}{{/literal}alert('{#acp_packageManager_selectAllPackages#}'); return false;{literal}}{/literal}" action="index.php?package=acp_packageManager&action=setQueueDetails" method="post">
<input type="submit" value="{#acp_packageManager_processQueue#}" />
{foreach from=$installQueue item=item}
{include file=$TPL_DIR|cat:"queueItem.tpl"}
{/foreach}
{literal}
<input type="checkbox" id="checkctrl" onchange="if(this.checked){checkboxes_checkAll(document.getElementsByName('update[]'));}else{checkboxes_uncheckAll(document.getElementsByName('update[]'));}" />
{/literal}
{#acp_packageManager_markAll#}
<input type="submit" value="{#acp_packageManager_processQueue#}" />
</form>
{include file=$FOOTER}