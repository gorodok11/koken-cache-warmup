<?php

/**
 * Koken cache warmup script.
 *
 * @author Sylvain Deloux <github@eax.fr>
 */

if ('cli' !== php_sapi_name()) {
    exit;
}

/**
 * Custom configuration goes here
 */

// Root dir of your Koken installation (contains i.php)
$rootDir = dirname(__FILE__);

// List of Koken internal images formats names
$formats = array('tiny', 'tiny.2x', 'small', 'small.2x', 'medium', 'medium.2x', 'medium_large', 'medium_large.2x', 'large', 'large.2x', 'xlarge', 'xlarge.2x', 'huge', 'huge.2x');

// Your website URL "http://www.xxxxxxxxx.com" (with no trailing slash)
$publicURL = '';

/**
 * End of configuration
 */

$storageDir = sprintf('%s%sstorage%s',       $rootDir,    DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
$configDir  = sprintf('%s%sconfiguration%s', $storageDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
$cachePath  = sprintf('%s/storage/cache/images', $publicURL);


/**
$databaseConfigFile = sprintf('%sdatabase.php', $configDir);

if (!file_exists($databaseConfigFile)) {
	printf("ERROR: Unable to find %s database config file. Make sure the Koken root dir is correctly configured.\n", $databaseConfig);

	exit(1);
}

require($databaseConfigFile);
 */
 
$KOKEN_DATABASE = Array(
      'driver' => 'mysqli',
      'hostname' => 'localhost',
      'database' => 'Koken',
      'username' => 'your_database_account',
      'password' => 'your_database_password',
      'prefix' => 'koken_',
      'socket' => ''
   ); 
 
try {
    $pdo = new PDO(sprintf('mysql:dbname=%s;host=%s', $KOKEN_DATABASE['database'], $KOKEN_DATABASE['hostname']), $KOKEN_DATABASE['username'], $KOKEN_DATABASE['password']);
} catch (PDOException $e) {
    printf("ERROR: Unable to access database (%s).\n", $e->getMessage());

    exit(1);
}

$imageQueryStatement = $pdo->prepare(sprintf('SELECT * FROM %scontent', $KOKEN_DATABASE['prefix']));

$images      = $imageQueryStatement->execute();
$imagesCount = $imageQueryStatement->rowCount();

if (0 === $imagesCount) {
    printf("No content found, exiting\n");

    exit(0);
}

$urlCount = $imagesCount * count($formats);

echo "\n";
echo "---------------------------------------------------------------------------\n";
echo "\n";
printf("Photos found : %d\n", $imagesCount);
printf("URLs to call : %d\n", $urlCount);
echo "\n";
echo "This script may be long to execute, depending on your content count.\n";
echo "\n";
echo "---------------------------------------------------------------------------\n";
echo "\n";

$i = 0;
while ($image = $imageQueryStatement->fetch(PDO::FETCH_ASSOC)) {
    $identifier = str_pad($image['id'], 6, 0, STR_PAD_LEFT);
    $parts      = str_split($identifier, 3);

    list($fileBasename, $fileExtension) = explode('.', $image['filename']);

    printf("%5.1f%% - %s ", $i * 100 / $imagesCount, $fileBasename);

    foreach ($formats as $format) {
        $url = sprintf('%s/%s/%s/%s,%s.%d.%s', $cachePath, $parts[0], $parts[1], $fileBasename, $format, $image['file_modified_on'], $fileExtension);
        
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $curlReturn = curl_exec($curl);

        curl_close($curl);

        echo '.';
    }

    echo "\n";

    $i++;
}
