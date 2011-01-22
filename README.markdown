# Spritzer #

Spritzer is a CSS sprite generator. Spritzer takes an input configuration file for the layout of the sprite and generates a
PNG file based on this layout. Spritzer also generates CSS `background` rules for each of the specified CSS selectors.

## Sprite format ##

* `@tile` defines the tile size for the table layout in the format `wxh` or `w,h`
* `@table` defines the table layout, indicating each character mapping to an image and CSS selector in the @images directive:

        a
        b
                    c
                    d
                    e

The resulting image size is dependent on the needed number of columns and rows defined in the table, and the tile size.

* `@images` defines each of the images and CSS selectors, in the following format:

        a = image-url.jpg: css-selector

## Usage: ##

    <?php
    $parser = new Spritzer_Parser('example.sprite');
    $sprite = $parser->parse();
    $sprite->image()->writeTo('images/sprite.png');
    $sprite->css('../images/sprite.png')->writeTo('css/sprite.css');
    

