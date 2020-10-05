<?php


namespace StenUserAvatar;


use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Repository;
use Shopware\Models\Media\Settings;

class StenUserAvatar extends Plugin
{
    const UPLOAD_ALBUM = 'avatar';
    const THUMBNAIL_SIZES = '50x50;80x80;300x300;400x400';

    /**
     * @param InstallContext $context
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function install(InstallContext $context)
    {

        $service = $this->container->get('shopware_attribute.crud_service');
        $modelManager = $this->container->get('models');
        $this->createMediaAlbum($modelManager);
        if (!$service->get('s_user_attributes', 'sten_avatar')) {
            $service->update('s_user_attributes', 'sten_avatar', 'string');
        }
    }

    /**
     * Creates the media album inclusive thumbnail settings etc
     *
     * @param ModelManager $modelManager
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createMediaAlbum(ModelManager $modelManager)
    {
        /** @var Repository $mediaRepository */
        $mediaRepository = $modelManager->getRepository(Album::class);

        /** @var Album $mediaAlbum */
        if ($mediaAlbum = $mediaRepository->findOneBy(['name' => self::UPLOAD_ALBUM])) {
            return;
        }

        $mediaAlbum = new Album();

        $mediaAlbum->setName(self::UPLOAD_ALBUM);
        $mediaAlbum->setPosition(9);

        $mediaAlbumSettings = new Settings();
        $mediaAlbumSettings->setCreateThumbnails(1);
        $mediaAlbumSettings->setIcon('sprite-user');
        $mediaAlbumSettings->setThumbnailHighDpi(false);
        $mediaAlbumSettings->setThumbnailSize(self::THUMBNAIL_SIZES);
        $mediaAlbumSettings->setThumbnailQuality(90);
        $mediaAlbumSettings->setThumbnailHighDpiQuality(60);

        $mediaAlbum->setSettings($mediaAlbumSettings);
        $mediaAlbumSettings->setAlbum($mediaAlbum);

        $modelManager->persist($mediaAlbum);
        $modelManager->persist($mediaAlbumSettings);
        $modelManager->flush([$mediaAlbum, $mediaAlbumSettings]);
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        $modelManager = $this->container->get('models');
        $service = $this->container->get('shopware_attribute.crud_service');
        $this->deleteMediaAlbum($modelManager);
        if ($service->get('s_user_attributes', 'sten_avatar')) {
            $service->delete('s_user_attributes', 'sten_avatar');
        }
    }

    private function deleteMediaAlbum(ModelManager $modelManager)
    {
        /** @var Repository $mediaRepository */
        $mediaRepository = $modelManager->getRepository(Album::class);

        /** @var Album $mediaAlbum */
        if ($mediaAlbum = $mediaRepository->findOneBy(['name' => 'Avatar'])) {
            $modelManager->remove($mediaAlbum);
            $modelManager->flush();
        }
    }
}
