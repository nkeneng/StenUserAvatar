{extends file="parent:frontend/index/main-navigation.tpl"}

{block name='frontend_index_navigation_categories_top_home'}
    {$smarty.block.parent}
    <li class="navigation--entry is--active is--home" role="menuitem">
        {block name='frontend_index_navigation_categories_top_link_home'}
            <a class="navigation--link is--first active" href="#" title="" aria-label="" itemprop="url">
                <span itemprop="name">Facebook</span>
            </a>
        {/block}
    </li>
{/block}
