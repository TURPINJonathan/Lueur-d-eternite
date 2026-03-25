<?php

namespace App\Controller\Admin;

use App\Entity\GalleryItem;
use App\Entity\Media;
use App\Service\Media\ImageThumbnailer;
use App\Service\Media\MediaGzipStorage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

#[AdminRoute(path: 'galerie', name: 'galerie')]
final class GalleryItemCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MediaGzipStorage $mediaGzipStorage,
        private readonly ImageThumbnailer $imageThumbnailer,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return GalleryItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image de galerie')
            ->setEntityLabelInPlural('Images de galerie')
            ->setPageTitle(Crud::PAGE_INDEX, 'Galerie')
            ->addFormTheme('admin/form_themes/gallery_item_masking.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        // Transients (srcFile/beforeFile/afterFile) are only present in forms.
        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            return [
                IdField::new('id')->hideOnForm(),

                BooleanField::new('isCompare', 'Ajouter une photo après ?')
                    ->renderAsSwitch()->setColumns(4),

                BooleanField::new('visibleInGallery', 'Visible dans la galerie')
                    ->renderAsSwitch()->setColumns(8),

                TextField::new('alt', 'Description')
                    ->setRequired(true)->setColumns(6),

                // Valeurs pour afficher un aperçu des images existantes sur la page d'édition
                // (elles sont utilisées par le form theme JS pour initialiser les prévisualisations).
                HiddenField::new('thumbUrl'),
                HiddenField::new('beforeSrcUrl'),
                HiddenField::new('afterSrcUrl'),

                ImageField::new('srcFile', 'Image unique')
                    ->setFormTypeOption('required', false)
                    ->setUploadDir('var/uploads/gallery/')
                    ->setUploadedFileNamePattern('[uuid].[extension]')
                    ->setColumns(6),

                ImageField::new('beforeFile', 'Photo avant')
                    ->setFormTypeOption('required', false)
                    ->setUploadDir('var/uploads/gallery/')
                    ->setUploadedFileNamePattern('[uuid].[extension]')
                    ->setColumns(6),

                ImageField::new('afterFile', 'Photo après')
                    ->setFormTypeOption('required', false)
                    ->setUploadDir('var/uploads/gallery/')
                    ->setUploadedFileNamePattern('[uuid].[extension]')
                    ->setColumns(6),
            ];
        }

        return [
            // IdField::new('id'),
            BooleanField::new('visibleInGallery', 'Visible dans la galerie'),
            ImageField::new('thumbUrl', 'Image unique'),
            ImageField::new('beforeSrcUrl', 'Photo avant'),
            ImageField::new('afterSrcUrl', 'Photo après'),
            TextField::new('alt', 'Description'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof GalleryItem) {
            return;
        }

        $this->populateMediaFromUploads($entityManager, $entityInstance, true);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof GalleryItem) {
            return;
        }

        $this->populateMediaFromUploads($entityManager, $entityInstance, false);

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function populateMediaFromUploads(
        EntityManagerInterface $entityManager,
        GalleryItem $item,
        bool $isCreate,
    ): void {
        $srcFile = $item->getSrcFile();
        $beforeFile = $item->getBeforeFile();
        $afterFile = $item->getAfterFile();

        $uploadDir = $this->projectDir . '/var/uploads/gallery/';

        $toSymfonyFile = function (?string $filename) use ($uploadDir): ?SymfonyFile {
            if (!$filename) {
                return null;
            }

            $path = $uploadDir . $filename;
            if (!is_file($path)) {
                return null;
            }

            return new SymfonyFile($path);
        };

        // Create gallery item => require correct file set based on kind.
        if ($isCreate) {
            if (!$item->isCompare() && !$srcFile) {
                throw new \RuntimeException('Image (src) est requise pour kind=single.');
            }
            if ($item->isCompare() && (!$beforeFile || !$afterFile)) {
                throw new \RuntimeException('Images avant + après sont requises pour kind=compare.');
            }
        }

        if (!$item->isCompare()) {
            if ($srcFile) {
                $src = $toSymfonyFile($srcFile);
                if (!$src) {
                    throw new \RuntimeException('Fichier src introuvable côté serveur.');
                }

                $srcMedia = $this->createMediaFromFile($entityManager, $src, $item->getAlt());
                $thumbMedia = $this->createThumbMediaFromFile($entityManager, $src, $item->getAlt()) ?? $srcMedia;

                $item->setSrcMedia($srcMedia);
                $item->setThumbMedia($thumbMedia);
                $item->setBeforeMedia(null);
                $item->setAfterMedia(null);

                @unlink($src->getPathname());
            }
        } else {
            if ($beforeFile) {
                $before = $toSymfonyFile($beforeFile);
                $after = $toSymfonyFile($afterFile);
                if (!$before || !$after) {
                    throw new \RuntimeException('Fichiers avant/après introuvables côté serveur.');
                }

                $beforeMedia = $this->createMediaFromFile($entityManager, $before, $item->getAlt());
                $thumbMedia = $this->createThumbMediaFromFile($entityManager, $before, $item->getAlt()) ?? $beforeMedia;

                $item->setBeforeMedia($beforeMedia);
                $item->setThumbMedia($thumbMedia);
            }

            if ($afterFile) {
                $after = $toSymfonyFile($afterFile);
                if (!$after) {
                    throw new \RuntimeException('Fichier après introuvable côté serveur.');
                }

                $afterMedia = $this->createMediaFromFile($entityManager, $after, $item->getAlt());
                $afterThumbMedia = $this->createThumbMediaFromFile($entityManager, $after, $item->getAlt()) ?? $afterMedia;
                $item->setAfterMedia($afterMedia);
                $item->setAfterThumbMedia($afterThumbMedia);

                @unlink($after->getPathname());
            }

            // Cleanup before file if it existed (we already created media+thumb from it).
            if ($beforeFile) {
                $before = $toSymfonyFile($beforeFile);
                if ($before) {
                    @unlink($before->getPathname());
                }
            }
        }

        // Clear transients so they won't be reused accidentally.
        $item->setSrcFile(null);
        $item->setBeforeFile(null);
        $item->setAfterFile(null);
    }

    private function createMediaFromFile(EntityManagerInterface $entityManager, SymfonyFile $file, ?string $alt): Media
    {
        $originalFilename = $file->getFilename() ?: 'upload';

        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'bin');

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $mediaId = Uuid::v4()->toRfc4122();
        $computed = $this->mediaGzipStorage->compressToGzip($mediaId, $file, $extension);

        $media = new Media($mediaId);
        $media
            ->setOriginalFilename($originalFilename)
            ->setStorageFilename($computed['storageFilename'])
            ->setMimeType($mimeType)
            ->setExtension($extension)
            ->setSizeOriginal($computed['originalSize'])
            ->setSizeCompressed($computed['sizeCompressed'])
            ->setSha256($computed['sha256'])
            ->setAlt($alt);

        $entityManager->persist($media);

        return $media;
    }

    private function createThumbMediaFromFile(EntityManagerInterface $entityManager, SymfonyFile $file, ?string $alt): ?Media
    {
        if (!str_starts_with($file->getMimeType() ?: '', 'image/')) {
            return null;
        }

        $realPath = $file->getRealPath();
        if (!$realPath) {
            return null;
        }

        $thumb = $this->imageThumbnailer->generateWebpThumbnail($realPath, $file->getMimeType() ?: 'image/jpeg');
        if (!$thumb) {
            return null;
        }

        $thumbMediaId = Uuid::v4()->toRfc4122();
        $thumbComputed = $this->mediaGzipStorage->compressFileToGzip($thumbMediaId, $thumb['path'], $thumb['extension']);

        $thumbMedia = new Media($thumbMediaId);
        $thumbMedia
            ->setOriginalFilename('thumb.webp')
            ->setStorageFilename($thumbComputed['storageFilename'])
            ->setMimeType($thumb['mimeType'])
            ->setExtension($thumb['extension'])
            ->setSizeOriginal($thumbComputed['originalSize'])
            ->setSizeCompressed($thumbComputed['sizeCompressed'])
            ->setSha256($thumbComputed['sha256'])
            ->setAlt($alt);

        $entityManager->persist($thumbMedia);

        @unlink($thumb['path']);

        return $thumbMedia;
    }
}

