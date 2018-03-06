#!/usr/bin/env php
<?php

# find_new_images.php: used to crawl through the images from a dump and the
# already existing images to find out which ones are new.
#
# This script takes two directories, `old' and `new' (in that order), and then
# compares every file from `old' to every file in `new'. It's therefore very
# slow. This is used whenever there's a new image dump from a Pokémon cartridge
# to see which icons are new and which ones are already present in the current
# version of Pokésprite.
#
# This script requires libpuzzle to be compiled and present.
# See <https://github.com/jedisct1/libpuzzle> for more information.

set_time_limit(0);

$verbose = false;
$show_hits = true;
$dir_old = @$argv[1];
$dir_new = @$argv[2];
$treshold = 0.066;
$GLOBALS['file_exts'] = array('jpg', 'png', 'jpeg', 'gif');

if (!isset($dir_old) || !isset($dir_new)) {
  print('usage: find_new_images.php old_dir new_dir'.PHP_EOL.'find_new_images.php: error: too few arguments'.PHP_EOL);
  exit();
}

if (!is_dir($dir_old) || !is_dir($dir_new)) {
  print('usage: find_new_images.php old_dir new_dir'.PHP_EOL.'find_new_images.php: error: old_dir or new_dir aren\'t directories'.PHP_EOL);
  exit();
}

$imgs_old = iterate_dir($dir_old);
$imgs_new = iterate_dir($dir_new);

$unique_new_imgs = array();

$counter = 0;
$total = count($imgs_old) * count($imgs_new);

print('old: `'.$dir_old.'\' contains '.count($imgs_old).' image files.'.PHP_EOL);
print('new: `'.$dir_new.'\' contains '.count($imgs_new).' image files.'.PHP_EOL);
print(PHP_EOL.'We will make '.(count($imgs_old) * count($imgs_new)).' comparisons.'.PHP_EOL);

if ($verbose) {
  print(PHP_EOL);
}
$counter_new = 0;
foreach ($imgs_new as $img_new_path => $img_new_info) {
  foreach ($imgs_old as $img_old_path => $img_old_info) {
    $diff = get_img_diff($img_new_path, $img_old_path);
    if ($verbose || $show_hits) {
      $perc = ($counter / $total);
      if ($perc >= 1) {
        $perc = ' 100%';
      }
      if ($perc < 1) {
        $perc = sprintf('%04.1f%%', $perc * 100);
      }
    }
    if ($verbose) {
      print('['.$perc.'] comparing: `'.$img_new_path.'\' to `'.$img_old_path.'\': diff: '.$diff.PHP_EOL);
    }
    $counter += 1;
    
    if ($diff < $treshold) {
      // Found duplicate. We're looking for images that do not have duplicates.
      $counter_new += 1;
      $counter = $counter_new * count($imgs_old);
      continue(2);
    }
  }
  if ($show_hits) {
    print('['.$perc.'] found unique image: `'.$img_new_path.'\'.'.PHP_EOL);
  }
  $counter_new += 1;
  $unique_new_imgs[$img_new_path] = $img_new_info;
}

if ($verbose || $show_hits) {
  print('[ 100%] done.'.PHP_EOL);
}
if (empty($unique_new_imgs)) {
  print(PHP_EOL.'No unique images found.');
}
else {
  print(PHP_EOL.'Amount of unique images found in `new\' directory: '.count($unique_new_imgs).PHP_EOL.PHP_EOL);
  foreach ($unique_new_imgs as $path => $img) {
    print('    '.$path.PHP_EOL);
  }
}
print(PHP_EOL);

function iterate_dir($dir)
{
  $stack = array();
  try {
    $dir_it = new \DirectoryIterator($dir);
  } catch (Exception $e) {
    print('error: can\'t open directory: '.$dir);
    continue;
  }
  foreach ($dir_it as $file) {
    // Some checks to ensure it's a valid image.
    if ($file->isDot()) {
      continue;
    }
    if ($file->isDir()) {
      $dir_stack = iterate_dir($dir.'/'.$file->getFilename());
      $stack = array_merge($dir_stack, $stack);
      continue;
    }
    $fn = $file->getFilename();
    $fn_bits = explode('.', $fn);
    $fn_ext = strtolower(trim(end($fn_bits)));
    $file_path = $dir.'/'.$fn;
    if (!in_array($fn_ext, $GLOBALS['file_exts'])) {
      continue;
    }
    $stack[$file_path] = true;
  }
  return $stack;
}

function get_img_diff($new, $old)
{
  $diff = trim(exec('puzzle-diff "'.$new.'" "'.$old.'" 2>&1', $output, $code));
  if ($code !== 0) {
    print(PHP_EOL.'find_new_images.php: error: couldn\'t run the `puzzle-diff\' script'.PHP_EOL);
    die();
  }
  return floatval($diff);
}