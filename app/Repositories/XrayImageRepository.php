<?php

namespace App\Repositories;

use App\Models\XrayImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class XrayImageRepository extends BaseRepository
{
    /**
     * XrayImageRepository constructor.
     *
     * @param XrayImage $model The X-ray image model instance.
     */
    public function __construct(XrayImage $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all X-ray images for a patient with optional type filter.
     *
     * @param int $patientId The patient ID.
     * @param string|null $type The image type filter.
     * @return Collection List of X-ray images.
     */
    public function getByPatient(int $patientId, ?string $type = null): Collection
    {
        $query = $this->model
            ->with(['doctor.user'])
            ->where('patient_id', $patientId)
            ->orderBy('xray_date', 'desc');

        if ($type) {
            $query->where('image_type', $type);
        }

        return $query->get();
    }

    /**
     * Find X-ray image by ID with all relationships.
     *
     * @param int $id The image ID.
     * @return XrayImage|null
     */
    public function findWithRelations(int $id): ?XrayImage
    {
        return $this->model
            ->with(['doctor.user', 'patient'])
            ->find($id);
    }

    /**
     * Create a new X-ray image with thumbnail generation.
     *
     * @param array $data The image data.
     * @param mixed $imageFile The uploaded image file.
     * @return XrayImage The created image.
     */
    public function createWithImage(array $data, mixed $imageFile): XrayImage
    {
        // Store original image
        $imagePath = $imageFile->store('xray_images', 'public');

        // Create thumbnail
        $thumbnailPath = 'xray_images/thumbs/' . basename($imagePath);
        $img = Image::make($imageFile->getRealPath());
        $img->fit(300, 300);
        $img->save(storage_path('app/public/' . $thumbnailPath));

        $data['file_path'] = $imagePath;
        $data['thumbnail_path'] = $thumbnailPath;

        return $this->create($data);
    }

    /**
     * Update X-ray image with optional new image.
     *
     * @param int $id The image ID.
     * @param array $data The update data.
     * @param mixed|null $imageFile The new uploaded image file.
     * @return XrayImage The updated image.
     */
    public function updateWithImage(int $id, array $data, mixed $imageFile = null): XrayImage
    {
        $xray = $this->findOrFail($id);

        if ($imageFile) {
            // Delete old files
            if ($xray->file_path) {
                Storage::disk('public')->delete($xray->file_path);
            }
            if ($xray->thumbnail_path) {
                Storage::disk('public')->delete($xray->thumbnail_path);
            }

            // Store new image
            $imagePath = $imageFile->store('xray_images', 'public');

            // Create new thumbnail
            $thumbnailPath = 'xray_images/thumbs/' . basename($imagePath);
            $img = Image::make($imageFile->getRealPath());
            $img->fit(300, 300);
            $img->save(storage_path('app/public/' . $thumbnailPath));

            $data['file_path'] = $imagePath;
            $data['thumbnail_path'] = $thumbnailPath;
        }

        $xray->update($data);

        return $xray->fresh();
    }

    /**
     * Delete X-ray image and its associated files.
     *
     * @param int $id The image ID.
     * @return bool True if deleted successfully.
     */
    public function deleteWithFiles(int $id): bool
    {
        $xray = $this->findOrFail($id);

        if ($xray->file_path) {
            Storage::disk('public')->delete($xray->file_path);
        }
        if ($xray->thumbnail_path) {
            Storage::disk('public')->delete($xray->thumbnail_path);
        }

        return $xray->delete();
    }

    /**
     * Get available image types.
     *
     * @return array List of image types.
     */
    public function getImageTypes(): array
    {
        return [
            ['id' => 'chest', 'name' => 'أشعة صدر'],
            ['id' => 'head', 'name' => 'أشعة رأس'],
            ['id' => 'teeth', 'name' => 'أشعة أسنان'],
            ['id' => 'spine', 'name' => 'أشعة عمود فقري'],
            ['id' => 'hand', 'name' => 'أشعة يد'],
            ['id' => 'foot', 'name' => 'أشعة قدم'],
            ['id' => 'joint', 'name' => 'أشعة مفصل'],
            ['id' => 'other', 'name' => 'أخرى'],
        ];
    }

    /**
     * Compare two X-ray images.
     *
     * @param int $id1 First image ID.
     * @param int $id2 Second image ID.
     * @return array|null Array with both images or null if not found.
     */
    public function compare(int $id1, int $id2): ?array
    {
        $xray1 = $this->find($id1);
        $xray2 = $this->find($id2);

        if (!$xray1 || !$xray2) {
            return null;
        }

        return [
            'xray1' => $xray1,
            'xray2' => $xray2,
        ];
    }
}
