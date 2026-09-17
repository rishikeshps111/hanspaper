<?php
namespace App\Models\Reels;
class ReelType extends ReelSetting
{
    protected static function booted(): void
    {
        parent::booted();
        static::updating(function (self $type) {
            if ($type->isDirty('volume') && Reel::where('reel_type_id', $type->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'volume' => 'This type already has reels. Create a separate type to use a different measurement.',
                ]);
            }
        });
    }
}
