<?php


namespace StenUserAvatar\Subscriber;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\AttributeBundle\Service\DataLoaderInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class ProfilSaveSubscriber implements SubscriberInterface
{

    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(RequestStack $requestStack,
                                ContainerInterface $container,
                                DataLoaderInterface $dataLoader,
                                ModelManager $modelManager
    )
    {
        $this->requestStack = $requestStack;
        $this->container = $container;
        $this->dataLoader = $dataLoader;
        $this->modelManager = $modelManager;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Controllers_Frontend_Account::saveProfileAction::before' => 'onBeforeSaveProfile'
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function onBeforeSaveProfile(\Enlight_Hook_HookArgs $args)
    {
        /** @var UploadedFile $file */
        $file = $this->requestStack->getCurrentRequest()->files->get("profile")['stenAvatar'];
//        $this->dumb($file);
        $userId = $this->container->get('session')->get('sUserId');

        $attribute = $this->dataLoader->load('s_user_attributes', $userId);

        /** @var Customer $customer */
        $customer = $this->container->get('models')->find(Customer::class, $userId);

        if ($customer) {
            $attribute = $customer->getAttribute();
        }
        $mediaRepository = $this->modelManager->getRepository(Album::class);

        /** @var Album $mediaAlbum */
        $mediaAlbum = $mediaRepository->findOneBy(['name' => 'Avatar']);

        $media = new Media();
        $media->setFile($file);
        $media->setAlbumId($mediaAlbum->getId());
        $media->setDescription($file->getClientOriginalName());
        $media->setUserId($userId);
        $media->setCreated(new \DateTime());

        $mediaAlbum->setMedia(new ArrayCollection([$media]));

        $this->modelManager->persist($media);
        $this->modelManager->persist($mediaAlbum);

        $attribute->setStenAvatar($file->getClientOriginalName());

        $this->modelManager->persist($attribute);

        $this->modelManager->flush();

    }

    function dumb($data)
    {
        /* error_log(_METHOD.'::'.LINE_.'::$filePath> '.print_r($filePath, 1)); */
        highlight_string("<?php\n " . var_export($data, true) . "?>");
        echo '<script>document.getElementsByTagName("code")[0].getElementsByTagName("span")[1].remove() ;document.getElementsByTagName("code")[0].getElementsByTagName("span")[document.getElementsByTagName("code")[0].getElementsByTagName("span").length - 1].remove() ; </script>';
        die();
    }

}
