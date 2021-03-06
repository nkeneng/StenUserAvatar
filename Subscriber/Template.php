<?php
/**
 * Copyright (c) Web Loupe. All rights reserved.
 * This file is part of software that is released
 * under a proprietary license. You must not
 * copy, modify, distribute, make publicly
 * available, or execute its contents or parts
 * thereof without express permission by the
 * copyright holder, unless otherwise permitted
 * by law.
 */

namespace StenUserAvatar\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Action;
use Enlight_Template_Manager;
use Shopware\Bundle\AttributeBundle\Service\DataLoaderInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Template
 *
 * @author    WEB LOUPE <shopware@webloupe.de>
 * @copyright Copyright (c) 2017-2020 WEB LOUPE
 * @package   WeloProductCrossSelling\Subscriber
 * @version   1
 */
class Template implements SubscriberInterface
{
    /**
     * @var Enlight_Template_Manager
     */
    private $templateManager;

    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    /**
     * @param                          $pluginDir
     * @param Enlight_Template_Manager $templateManager
     * @param ContainerInterface $container
     * @param DataLoaderInterface $dataLoader
     */
    public function __construct(
        $pluginDir,
        Enlight_Template_Manager $templateManager,
        ContainerInterface $container,
        DataLoaderInterface $dataLoader
    )
    {
        $this->templateManager = $templateManager;
        $this->pluginDir = $pluginDir;
        $this->container = $container;
        $this->dataLoader = $dataLoader;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' => 'onPreDispatch',
        ];
    }


    public function onPreDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        $this->templateManager->addTemplateDir($this->pluginDir . '/Resources/views');

        $userId = (int)$this->container->get('session')->get('sUserId');

        if (0 == $userId) {
            return;
        }

        $attribute = $this->dataLoader->load('s_user_attributes', $userId);
        $avatarUrl = "media/image/" . $attribute['sten_avatar'];

        $view->assign('StenAvatarUrl', $avatarUrl);
    }
}
