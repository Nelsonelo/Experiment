# Unzipper Utility Overview

The Unzipper is a convenient tool designed to extract .zip, .rar, and .gz/.tar.gz archives directly on web servers. It automatically detects available archives and allows you to choose which one to extract, even if multiple files are present. 

## Requirements

PHP version 5.3 or higher (Note: It’s highly recommended to upgrade if you are using PHP versions older than 8.0.30, as they no longer receive security updates, leaving your site vulnerable).

## Usage

* Download the unzipper.php file and upload it to the same directory as your .zip, .rar, or .gz archive.
* In your browser, navigate to the URL where unzipper.php is located.

### To unzip an archive

* Select the .zip, .rar, or .gz file you wish to extract.
* Optionally, choose the extraction path (by default, it will extract to the current directory).
* Click "Extract" to start the extraction.

### To create a zip archive

* Optionally, set the path to the source folder (by default, it will zip the current directory).
* Click "Create Zip" to create the archive.

![Screenshot](https://github.com/Nelsonelo/unzipper/blob/70629c3f20b9457f0b776cbc11748352de25ef1a/Screenshot.png)

