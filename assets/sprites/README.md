PokéSprite – Image Sprite Generator
===================================

This simple script generates a *complete image sprite* of all Pokémon in the National Pokédex, along with the icons for every single in-game item. It also generates *SCSS and JS files* which can then be used to efficiently display the icons from the sprite on a website.

<p align="center">
  <img src="https://raw.github.com/msikma/pokesprite/master/resources/wiki/pokesprite-banner.png" alt="PokéSprite icon example" />
</p>

Usage guide
-----------

Displaying the sprites is a matter of adding an empty `<span>` or `<div>` element with the appropriate `class` attribute set, and then running the JS decoration code. The base class is `pkspr`. Following the base class, you can add a number of classes that specify which icon is to be displayed.

Here are some examples:

```html
<span class="pkspr pkmn-pikachu"></span>
<span class="pkspr pkmn-bulbasaur color-shiny"></span>
<span class="pkspr pkmn-deoxys form-defense"></span>
<span class="pkspr pkmn-clauncher color-shiny dir-right"></span>
<span class="pkspr pkmn-charizard form-mega-y"></span>
<span class="pkspr pkmn-unown form-d"></span>
<span class="pkspr pkmn-pyroar gender-female"></span>
```

To clarify, the following classes can be used:

* <code>pkmn-<strong>name</strong></code> – Pokémon name* or Pokédex number
* <code>color-regular</code>, <code>color-shiny</code> – shiny or regular icon
* <code>dir-left</code>, <code>dir-right</code> – direction the icon faces (some Pokémon, such as Roselia, have a different icon when facing right—by default, those that do not have a separate icon will be flipped using the CSS `transform` attribute)
* <code>gender-male</code>, <code>gender-female</code> – gender of the icon (in case of gender differences, such as Meowstic)
* <code>form-<strong>name</strong></code> – form of the Pokémon (e.g. `defense` for Deoxys, `a` or `exclamation` for Unown, `orange` for Flabébé, etc.)

\**Note: for Pokémon names, simplified versions without special characters are used, e.g. "flabebe" rather than "Flabébé". See the [icon overview page](http://msikma.github.io/pokesprite/build/overview.html) for a full list of supported names.*

The tag name used is also important: if a `<span>` is used, the icon is displayed as an `inline-block`. If a `<div>` is used, it's a `block`.

### Item icons

The item icons have been organized in a set of collections. To display an icon, first the collection name must be used, followed by the item itself. For example, an Oran Berry is named `oran` and is in the `berry` collection, so the full class name would be `pkspr berry-oran`. Some more HTML examples follow:

```html
<span class="pkspr berry-oran"></span>
<span class="pkspr body-style-bipedal-tailed"></span>
<span class="pkspr fossil-helix"></span>
<span class="pkspr gem-bug"></span>
<span class="pkspr medicine-potion"></span>
<span class="pkspr mega-stone-charizardite-y"></span>
<span class="pkspr pokeball-dive"></span>
<span class="pkspr tm-ice"></span>
```

There are many different icons that can be displayed. See the [icon overview page](http://msikma.github.io/pokesprite/build/overview.html) for a complete overview.

### Decorating the icons

The icons will not yet show up until you run the decoration JS code. There are two ways to activate the code: either with a single command that is added before the closing `</body>` tag (or, anywhere after the last icon):

```html
<script type="text/javascript">
PkSpr.process_dom();
</script>
```

Or, you can add the following after each individual icon. This ensures the icons show up as soon as possible, rather than showing them all at once after loading is complete:

```html
<span id="icon_1" class="pkspr pkmn-bulbasaur color-shiny"></span>
<script type="text/javascript">
PkSpr.decorate('icon_1');
</script>
```

Compiling the sprite
--------------------

Running the script to generate a sprite image with default settings is a simple matter of running the program.

```
./pokesprite.php
```

This will generate a full sprite sheet with regular icons, shiny icons, right-facing icons (where a unique icon exists), and all other icon sets. It also generates SCSS and JS files and an overview HTML page for previewing your build. Everything is saved to the `output/` directory.

Normally you don't need to do this, since you can just use a pre-compiled version instead.

### Compiling SCSS to CSS

PokéSprite does not generate CSS—it only generates SCSS (which can't directly be used in a website). You'll have to compile the CSS yourself using [SASS](https://github.com/sass/sass). See the SASS manual for a more complete usage guide.

Once you have SASS installed, the CSS file can be compiled using the following terminal command:

```
sass --style compressed output/pokesprite.scss output/pokesprite.css
```

The generated SCSS is currently not SassC compatible. This is planned for a later release.

### Compiling the JS using the Closure Compiler

The JS file can be optimized with the Closure Compiler. The easiest way is to use the [Closure Compiler Service](http://closure-compiler.appspot.com/home). Make sure to set the optimization level to *advanced*.

In case you have a local binary, the following command can be used:

```
java -jar closure-compiler.jar --compilation_level ADVANCED_OPTIMIZATIONS \
  --js output/pokesprite.js --js_output_file output/pokesprite.min.js \
  --charset UTF-8
```

License
-------

The source icons are © Nintendo/Creatures Inc./GAME FREAK Inc.

Everything else, and usage of the programming code, is governed by the [MIT license](http://opensource.org/licenses/MIT).
