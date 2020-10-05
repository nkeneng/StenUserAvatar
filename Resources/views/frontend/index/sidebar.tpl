{extends file="parent:frontend/index/sidebar.tpl"}

{block name='frontend_index_left_categories_wrapper'}
    <div class="logo--shop block">
            <picture>
                <source srcset="{media path={$StenAvatarUrl}}" media="(min-width: 78.75em)">
                <source srcset="{media path={$StenAvatarUrl}}" media="(min-width: 64em)">
                <source srcset="{media path={$StenAvatarUrl}}" media="(min-width: 48em)">
                <img src="{media path={$StenAvatarUrl}}" alt="">
            </picture>
    </div>
    {$smarty.block.parent}
{/block}
