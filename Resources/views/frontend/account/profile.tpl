{extends file="parent:frontend/account/profile.tpl"}

{block name="frontend_account_profile_profile_body"}
    {$smarty.block.parent}
    <div class="panel--body is--wide">
        <input  type="file"
               id="avatar" name="profile[stenAvatar]"
               accept="image/png, image/jpeg">
    </div>
{/block}
{block name="frontend_account_profile_profile_actions_submit"}
    <button formenctype="multipart/form-data" class="btn is--block is--primary" type="submit" data-preloader-button="true">
        {s name="ProfileSaveButton"}{/s}
    </button>
{/block}
