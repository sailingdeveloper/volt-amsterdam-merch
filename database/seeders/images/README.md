# Seed Images

Place product images in this directory to have them automatically copied to storage when running the database seeder.

## Expected files

- `hoodie-purple-front.jpg` - Hoodie (Purple) main image
- `hoodie-purple-back.jpg` - Hoodie (Purple) back image
- `hoodie-white-front.jpg` - Hoodie (White) main image
- `hoodie-white-back.jpg` - Hoodie (White) back image
- `longsleeve-purple-front.jpg` - Long-Sleeve Shirt (Purple) main image
- `longsleeve-purple-back.jpg` - Long-Sleeve Shirt (Purple) back image
- `longsleeve-white-front.jpg` - Long-Sleeve Shirt (White) main image
- `longsleeve-white-back.jpg` - Long-Sleeve Shirt (White) back image
- `scarf-white.jpg` - Scarf (White) main image

## Usage

1. Add your product images to this directory with the filenames listed above.
2. Run `php artisan migrate:fresh --seed` to reset and seed the database.
3. The images will be automatically copied to `storage/app/public/products/`.

Note: Make sure you have run `php artisan storage:link` to create the symbolic link for public storage.
