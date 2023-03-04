<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php if ($headerNavigation || $headerCategories) {
    $getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false;

    if (count($headerNavigation)) {
        $noOfCharAllowedInNav = 90;
        $rightNavCharCount = 5;
        if (!$isUserLogged) {
            $rightNavCharCount = $rightNavCharCount + mb_strlen(html_entity_decode(Labels::getLabel('LBL_Sign_In', $siteLangId), ENT_QUOTES, 'UTF-8'));
        } else {
            $rightNavCharCount = $rightNavCharCount + mb_strlen(html_entity_decode(Labels::getLabel('LBL_Hi,', $siteLangId) . ' ' . $userName, ENT_QUOTES, 'UTF-8'));
        }
        $rightNavCharCount = $rightNavCharCount + mb_strlen(html_entity_decode(Labels::getLabel("LBL_Cart", $siteLangId), ENT_QUOTES, 'UTF-8'));
        $noOfCharAllowedInNav = $noOfCharAllowedInNav - $rightNavCharCount;

        $navLinkCount = 0;
        foreach ($headerNavigation as $nav) {
            if (!$nav['pages']) {
                break;
            }
            foreach ($nav['pages'] as $link) {
                $noOfCharAllowedInNav = $noOfCharAllowedInNav - mb_strlen(html_entity_decode($link['nlink_caption'], ENT_QUOTES, 'UTF-8'));
                if ($noOfCharAllowedInNav < 0) {
                    break;
                }
                $navLinkCount++;
            }
        }
    }

?>
    <div class="last-bar no-print">
        <div class="container">
            <div class="navigations__overlayx"></div>
            <div class="navigation-wrapper">
                <ul class="navigations <?php echo ($navLinkCount > 4) ? 'justify-content-between' : ''; ?>">
                    <?php if (count($headerCategories)) { ?>
                        <li class="navchild categories">
                            <a class="categories-link" href="javascript:void(0)">
                                <i class="c-hamburger js-hamburger">
                                    <span class="c-hamburger__line c-hamburger__line--top"></span>
                                    <span class="c-hamburger__line c-hamburger__line--middle"></span>
                                    <span class="c-hamburger__line c-hamburger__line--bottom"></span>
                                </i>

                                <?php echo Labels::getLabel('LBL_Shop_by_Categories', $siteLangId); ?>

                            </a>
                            <span class="link__mobilenav"></span>
                            <div class="vertical-menu">
                                <ul class="menu">
                                    <?php
                                    $count = 0;
                                    foreach ($headerCategories as $link) {
                                        $count++;
                                        if ($count > 9) {
                                            break;
                                        }
                                        $navUrl = UrlHelper::generateUrl('category', 'view', array($link['prodcat_id']));
                                        $OrgnavUrl = UrlHelper::generateUrl('category', 'view', array($link['prodcat_id']), '', null, false, $getOrgUrl);

                                        $href = $navUrl;
                                        $navchild = '';
                                        $class = '';
                                        if (0 < count($link['children'])) {
                                            // $href = 'javascript:void(0)';
                                            $navchild = 'navchild';
                                            $class = 'has-child';
                                        } ?>
                                        <li class="<?php echo $class; ?>"><a data-org-url="<?php echo $OrgnavUrl; ?>" href="<?php echo $href; ?>"><?php echo $link['prodcat_name']; ?></a>
                                            <?php if (isset($link['children']) && count($link['children']) > 0) { ?>
                                                <div class="megadrop">
                                                    <ul class="sublinks">
                                                        <?php $subyChild = 0;
                                                        foreach ($link['children'] as $children) {
                                                            $subCatUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']));
                                                            $subCatOrgUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']), '', null, false, $getOrgUrl);
                                                        ?>
                                                            <li class="head"><a class="level2" data-org-url="<?php echo $subCatOrgUrl; ?>" href="<?php echo $subCatUrl; ?>"><?php echo $children['prodcat_name']; ?></a>
                                                                <?php if (isset($children['children']) && count($children['children']) > 0) { ?>
                                                                    <ul>
                                                                        <?php $subChild = 0;
                                                                        foreach ($children['children'] as $childCat) {
                                                                            $catUrl = UrlHelper::generateUrl('category', 'view', array($childCat['prodcat_id']));
                                                                            $catOrgUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']), '', null, false, $getOrgUrl);
                                                                        ?>
                                                                            <li><a data-org-url="<?php echo $catOrgUrl; ?>" href="<?php echo $catUrl; ?>"><?php echo $childCat['prodcat_name']; ?></a>
                                                                            </li>
                                                                            <?php
                                                                            if ($subChild++ == 4) {
                                                                                break;
                                                                            }
                                                                        }
                                                                        if (count($children['children']) > 5) { ?>
                                                                            <li class="seemore"><a data-org-url="<?php echo $subCatOrgUrl; ?>" href="<?php echo $subCatUrl; ?>"><?php echo Labels::getLabel('LBL_View_All', $siteLangId); ?></a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                <?php } ?>
                                                            </li>
                                                        <?php
                                                            if ($subyChild++ == 7) {
                                                                break;
                                                            }
                                                        } ?>
                                                    </ul>
                                                    <?php if (count($link['children']) > 8) { ?>
                                                        <a class="btn btn-sm btn--secondary ripplelink " data-org-url="<?php echo $OrgnavUrl; ?>" href="<?php echo $navUrl; ?>"><?php echo Labels::getLabel('LBL_View_All', $siteLangId); ?></a>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </li>
                                    <?php } ?>
                                    <li class="all-categories"><a data-org-url="<?php echo UrlHelper::generateUrl('category', '', array(), '', null, false, $getOrgUrl); ?>" href="<?php echo UrlHelper::generateUrl('category'); ?>" class=""><?php echo Labels::getLabel('LBL_View_All_Categories', $siteLangId); ?></a></li>
                                </ul>
                            </div>
                        </li>
                    <?php } ?>
                    <?php
                    if (count($headerNavigation)) {
                        foreach ($headerNavigation as $nav) {
                            if ($nav['pages']) {
                                $mainNavigation = array_slice($nav['pages'], 0, $navLinkCount);
                                foreach ($mainNavigation as $link) {
                                    $navUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id']);
                                    $OrgnavUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id'], $getOrgUrl);

                                    $href = $navUrl;
                                    $navchild = '';
                                    $target = $link['nlink_target'];
                                    if (0 < count($link['children'])) {
                                        $href = 'javascript:void(0)';
                                        $navchild = 'navchild';
                                        $target = '_self';
                                    }
                    ?>
                                    <li class=" <?php echo $navchild; ?>">
                                        <a target="<?php echo $target; ?>" data-org-url="<?php echo $OrgnavUrl; ?>" href="<?php echo $href; ?>"><?php echo $link['nlink_caption']; ?></a>
                                        <?php if (isset($link['children']) && count($link['children']) > 0) { ?>
                                            <span class="link__mobilenav"></span>
                                            <div class="subnav">
                                                <div class="subnav__wrapper ">
                                                    <div class="container">
                                                        <div class="subnav_row">
                                                            <ul class="sublinks">
                                                                <?php $subyChild = 0;
                                                                foreach ($link['children'] as $children) {
                                                                    $subCatUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']));
                                                                    $subCatOrgUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']), '', null, false, $getOrgUrl);
                                                                ?>
                                                                    <li class="head"><a class="level2" data-org-url="<?php echo $subCatOrgUrl; ?>" href="<?php echo $subCatUrl; ?>"><?php echo $children['prodcat_name']; ?></a>
                                                                        <?php if (isset($children['children']) && count($children['children']) > 0) { ?>
                                                                            <ul>
                                                                                <?php $subChild = 0;
                                                                                foreach ($children['children'] as $childCat) {
                                                                                    $catUrl = UrlHelper::generateUrl('category', 'view', array($childCat['prodcat_id']));
                                                                                    $catOrgUrl = UrlHelper::generateUrl('category', 'view', array($children['prodcat_id']), '', null, false, $getOrgUrl);
                                                                                ?>
                                                                                    <li><a data-org-url="<?php echo $catOrgUrl; ?>" href="<?php echo $catUrl; ?>"><?php echo $childCat['prodcat_name']; ?></a>
                                                                                    </li>
                                                                                    <?php
                                                                                    if ($subChild++ == 4) {
                                                                                        break;
                                                                                    }
                                                                                }
                                                                                if (count($children['children']) > 5) { ?>
                                                                                    <li class="seemore"><a data-org-url="<?php echo $subCatOrgUrl; ?>" href="<?php echo $subCatUrl; ?>"><?php echo Labels::getLabel('LBL_View_All', $siteLangId); ?></a>
                                                                                    </li>
                                                                                <?php } ?>
                                                                            </ul>
                                                                        <?php } ?>
                                                                    </li>
                                                                <?php
                                                                    if ($subyChild++ == 7) {
                                                                        break;
                                                                    }
                                                                } ?>
                                                            </ul>
                                                            <?php if (count($link['children']) > 8) { ?>
                                                                <a class="btn btn-sm btn--secondary ripplelink " data-org-url="<?php echo $OrgnavUrl; ?>" href="<?php echo $navUrl; ?>"><?php echo Labels::getLabel('LBL_View_All', $siteLangId); ?></a>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </li>
                    <?php
                                }
                            }
                        }
                    } ?>






                    <?php
                    foreach ($headerNavigation as $nav) {
                        $subMoreNavigation = (count($nav['pages']) > $navLinkCount) ? array_slice($nav['pages'], $navLinkCount) : array();

                        if (count($subMoreNavigation)) {    ?>
                            <li class="navchild three-pin">
                                <a href="javascript:void(0)" class="more"><span><?php echo Labels::getLabel('L_More', $siteLangId); ?></span>
                                </a>
                                <span class="link__mobilenav"></span>
                                <div class="subnav">
                                    <div class="subnav__wrapper ">
                                        <div class="container">
                                            <div class="subnav_row">
                                                <ul class="sublinks">
                                                    <?php
                                                    foreach ($subMoreNavigation as $index => $link) {
                                                        $url = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id']);
                                                        $OrgUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id'], $getOrgUrl);
                                                    ?>
                                                        <li><a target="<?php echo $link['nlink_target']; ?>" data-org-url="<?php echo $OrgUrl; ?>" href="<?php echo $url; ?>"><?php echo $link['nlink_caption']; ?></a>
                                                            <?php
                                                            if (count($link['children']) > 0) { ?>
                                                                <ul>
                                                                    <?php foreach ($link['children'] as $subCat) {
                                                                        $catUrl = UrlHelper::generateUrl('category', 'view', array($subCat['prodcat_id']));
                                                                        $catOrgUrl = UrlHelper::generateUrl('category', 'view', array($subCat['prodcat_id']), '', null, false, $getOrgUrl); ?>
                                                                        <li><a data-org-url="<?php echo $catOrgUrl; ?>" href="<?php echo $catUrl; ?>"><?php echo $subCat['prodcat_name']; ?></a></li>
                                                                        <?php /*if (isset($subCat['children'])) { ?>
                                            <ul>
                                                        <?php
                                                        $subChild = 0;
                                                        foreach ($subCat['children'] as $childCat) {
                                                            $childCatUrl = UrlHelper::generateUrl('category', 'view', array( $childCat['prodcat_id']));
                                                            $childCatOrgUrl = UrlHelper::generateUrl('category', 'view', array( $childCat['prodcat_id']), '', null, false, $getOrgUrl); ?>
                                                <li><a data-org-url="<?php echo $childCatOrgUrl; ?>" href="<?php echo $childCatUrl; ?>"><?php echo $childCat['prodcat_name'];?></a></li>
                                                            <?php
                                                            if ($subChild++ == 4) {
                                                                    break;
                                                            }
                                                        }
                                                        if (count($subCat['children']) > 5) {?>
                                                    <li class="seemore"><a data-org-url="<?php echo $catOrgUrl; ?>"
                                                            href="<?php echo $catUrl;?>"><?php echo Labels::getLabel('LBL_View_All', $siteLangId);?></a>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                                <?php }*/ ?>
                                                                    <?php } ?>
                                                                </ul>
                                                            <?php } ?>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                                <?php /* <a data-org-url="<?php echo UrlHelper::generateUrl('category', '', array(), '', null, false, $getOrgUrl); ?>" href="<?php echo UrlHelper::generateUrl('category');?>"
                                        class="btn btn-sm btn--secondary ripplelink "><?php Labels::getLabel('LBL_View_All_Categories', $siteLangId);?></a> */ ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                    <?php }
                    } ?>
                    <?php if ($top_header_navigation && count($top_header_navigation)) { ?>
                        <?php foreach ($top_header_navigation as $nav) {
                            if ($nav['pages']) {
                                $getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false;
                                foreach ($nav['pages'] as $link) {
                                    $navUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id']);
                                    $OrgnavUrl = CommonHelper::getnavigationUrl($link['nlink_type'], $link['nlink_url'], $link['nlink_cpage_id'], $link['nlink_category_id'], $getOrgUrl); ?>
                                    <li class="d-xl-none"><a target="<?php echo $link['nlink_target']; ?>" data-org-url="<?php echo $OrgnavUrl; ?>" href="<?php echo $navUrl; ?>"><?php echo $link['nlink_caption']; ?></a></li>
                        <?php }
                            }
                        } ?>
                    <?php } ?>
                </ul>

            </div>
        </div>
    </div>
<?php } ?>