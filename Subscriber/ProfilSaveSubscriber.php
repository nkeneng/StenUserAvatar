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
//        $mediaService = $this->container->get('shopware_media.media_service');
//        $fileExists = $mediaService->has('media/image/' . $file->getClientOriginalName());
//        // if file already there skip
//        if ($fileExists) {
//            return;
//        }

        // get userId from session to check if user is connected
        $userId = $this->container->get('session')->get('sUserId');

        if (!$userId) {
            return;
        }
        /** @var Customer $customer */
        $customer = $this->container->get('models')->find(Customer::class, $userId);

        if (!$customer) {
            return;
        }
        $attribute = $customer->getAttribute();

        $albumRepository = $this->modelManager->getRepository(Album::class);

        /** @var Album $mediaAlbum */
        $mediaAlbum = $albumRepository->findOneBy(['name' => 'Avatar']);

        $mediaRepository = $this->modelManager->getRepository(Media::class);
        $media = $mediaRepository->findOneBy(['userId' => $userId]);

        // if no media create a new one otherwise use the existing one
        if (!$media) {
            $media = new Media();
            $media->setUserId($userId);
            $media->setAlbumId($mediaAlbum->getId());
        }

        $media->setFile($file);
        $media->setDescription($file->getClientOriginalName());
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
