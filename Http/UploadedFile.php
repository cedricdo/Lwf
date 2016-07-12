<?php

declare(strict_types = 1);

namespace Lwf\Http;
use Lwf\Http\Exception\RuntimeException;

/**
 * Represent a file uploaded within http request
 */
class UploadedFile
{
    /** @var string  */
    private $name;
    /** @var string  */
    private $type;
    /** @var string  */
    private $temporaryName;
    /** @var int  */
    private $errorCode;
    /** @var int  */
    private $size;

    /**
     * Constructor.
     *
     * @param string $name The file's name
     * @param string $type The file's mime type
     * @param string $temporaryName The temporary name of the file on disk
     * @param int    $errorCode The error code according to {@see http://php.net/manual/fr/features.file-upload.errors.php}
     * @param int    $size The file's size
     */
    public function __construct(
        string $name, string $type, string $temporaryName, int $errorCode, int $size
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->temporaryName = $temporaryName;
        $this->errorCode = $errorCode;
        $this->size = $size;
    }

    /**
     * Move the uploaded file to final destination
     *
     * @param string $destination The destination path
     *
     * @thows RuntimeException If the move fails
     */
    public function moveTo(string $destination)
    {
        if (false === move_uploaded_file($this->temporaryName, $destination)) {
            throw new RuntimeException(sprintf(
                "Can't move uploaded file %s to %s", $this->temporaryName, $destination
            ));
        }
    }

    /**
     * Indicates if the upload is successful
     *
     * @return bool
     */
    public function isUploadSuccessful(): bool
    {
        return $this->errorCode == UPLOAD_ERR_OK;
    }

    /**
     * Return the file's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the file's mime type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Return the file's temporary name on disk
     *
     * @return string
     */
    public function getTemporaryName(): string
    {
        return $this->temporaryName;
    }

    /**
     * Return the upload error code
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Return the size of the uploaded file
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
}
