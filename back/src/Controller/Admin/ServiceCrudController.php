<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use App\Entity\Media;
use App\Service\Media\MediaGzipStorage;
use App\Service\Media\ImageThumbnailer;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

#[AdminRoute(path: 'services', name: 'services')]
final class ServiceCrudController extends AbstractCrudController
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
        return Service::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Service')
            ->setEntityLabelInPlural('Services')
            ->setPageTitle(Crud::PAGE_INDEX, 'Services')
            ->addFormTheme('admin/form_themes/service_prestations_widget.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            return [
                IdField::new('id')->hideOnForm(),

                TextField::new('title', 'Titre')
                    ->setRequired(true)
                    ->setColumns(6),

                TextField::new('subtitle', 'Sous-titre')
                    ->setRequired(true)
                    ->setColumns(6),

                    
                // URL actuelle pour l’aperçu (script service_card_image_preview.html.twig).
                HiddenField::new('pictureUrl'),
                
                TextareaField::new('itemsText', 'Prestations (une par ligne)')
                ->setRequired(true)
                ->setColumns(12),
                
                TextField::new('pictureAlt', 'Description de l\'image')
                    ->setRequired(true)
                    ->setColumns(6),

                ImageField::new('imageFile', 'Image')
                    ->setFormTypeOption('required', Crud::PAGE_NEW === $pageName)
                    ->setUploadDir('var/uploads/services/')
                    ->setUploadedFileNamePattern('[uuid].[extension]')
                    ->setColumns(6),
            ];
        }

        return [
            ImageField::new('pictureUrl', 'Image'),
            TextField::new('title', 'Titre'),
            TextField::new('subtitle', 'Sous-titre'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Service) {
            $this->populateMediaFromUploads($entityManager, $entityInstance, true);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Service) {
            $this->populateMediaFromUploads($entityManager, $entityInstance, false);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function populateMediaFromUploads(
        EntityManagerInterface $entityManager,
        Service $item,
        bool $isCreate,
    ): void {
        $imageFile = $item->getImageFile();

        $uploadDir = $this->projectDir . '/var/uploads/services/';

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

        if ($isCreate && !$imageFile) {
            throw new \RuntimeException('Image est requise pour créer un service.');
        }

        if ($imageFile) {
            $file = $toSymfonyFile($imageFile);
            if (!$file) {
                throw new \RuntimeException('Fichier image introuvable côté serveur.');
            }

            $pictureAlt = $item->getPictureAlt() ?: $item->getTitle();
            $srcMedia = $this->createMediaFromFile($entityManager, $file, $pictureAlt);
            $thumbMedia = $this->createThumbMediaFromFile($entityManager, $file, $pictureAlt) ?? $srcMedia;

            $item->setPictureMedia($thumbMedia);

            @unlink($file->getPathname());
        }

        // Clear transient property.
        $item->setImageFile(null);
    }

    private function createMediaFromFile(EntityManagerInterface $entityManager, SymfonyFile $file, ?string $alt): Media
    {
        $originalFilename = $file->getFilename() ?: 'upload';

        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'bin');
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $mediaId = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
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

        $thumbMediaId = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
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

