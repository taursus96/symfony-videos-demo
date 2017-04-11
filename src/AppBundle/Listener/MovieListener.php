<?php

namespace AppBundle\Listener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use AppBundle\Entity\Movie;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Filesystem\Filesystem;

class MovieListener
{
    protected $moviesDirectory;
    protected $previewsDirectory;
    protected $em;
    protected $uow;

    public function __construct(string $moviesDirectory, string $previewsDirectory)
    {
        $this->moviesDirectory = $moviesDirectory;
        $this->previewsDirectory = $previewsDirectory;
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->em = $eventArgs->getEntityManager();
        $this->uow = $this->em->getUnitOfWork();

        foreach ($this->uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Movie) {
                $this->processEntity($entity);
            }
        }

        foreach ($this->uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Movie) {
                $this->processEntity($entity);
            }
        }

        foreach ($this->uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Movie) {
                $this->onDeleteEntity($entity);
            }
        }
    }

    protected function onDeleteEntity(Movie $movie)
    {
        $fs = new Filesystem();
        $fs->remove($this->moviesDirectory . $movie->getFile());
        $fs->remove($this->previewsDirectory . $movie->getPreview());
    }

    protected function processEntity(Movie $movie)
    {
        $this->processFile($movie);
        $this->processPreview($movie);

        $this->uow->recomputeSingleEntityChangeSet($this->em->getClassMetadata(Movie::class), $movie);
    }

    protected function processFile(Movie $movie)
    {
        $file = $movie->getFile();
        if ($file instanceof UploadedFile) {
            $fileName = uniqid() . '.' . $file->guessExtension();
            $file->move($this->moviesDirectory, $fileName);
            $movie->setFile($fileName);
        }
    }

    protected function processPreview(Movie $movie)
    {
        $preview = $movie->getPreview();
        if ($preview instanceof UploadedFile) {
            $previewName = uniqid() . '.' . $preview->guessExtension();
            $preview->move($this->previewsDirectory, $previewName);
            $movie->setPreview($previewName);
        }
    }
}
