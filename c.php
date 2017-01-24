<?php
/**
 * Created by PhpStorm.
 * User: Thomas Müller
 * Date: 24.01.2017
 * Time: 07:24
 */

include 'vendor/autoload.php';

$sourceDirectory = 'tests/issues/';

$adapter      = new \League\Flysystem\Adapter\Local('cache/');
$cache        = new \Cache\Adapter\Filesystem\FilesystemCachePool(new \League\Flysystem\Filesystem($adapter));

$iterator = new \RecursiveDirectoryIterator($sourceDirectory);

$companies    = json_decode(file_get_contents('vendor/mimmi20/browser-detector/data/companies.json'));
$browserTypes = json_decode(file_get_contents('vendor/mimmi20/ua-browser-type/data/types.json'));
$deviceTypes  = json_decode(file_get_contents('vendor/mimmi20/ua-device-type/data/types.json'));

//foreach (new \RecursiveIteratorIterator($iterator) as $file) {
//    /** @var $file \SplFileInfo */
//    if (!$file->isFile() || $file->getExtension() !== 'json') {
//        continue;
//    }
//
//    echo 'handling ', $file->getPathname(), ' ...', PHP_EOL;
//
//    $tests = json_decode(file_get_contents($file->getPathname()));
//
//    foreach ($tests as $key => $test) {
//        foreach ($companies as $companyKey => $companyData) {
//            if ($test->properties->RenderingEngine_Maker === $companyData->name) {
//                $test->properties->RenderingEngine_Maker = $companyKey;
//            }
//
//            if ($test->properties->Browser_Maker === $companyData->name) {
//                $test->properties->Browser_Maker = $companyKey;
//            }
//
//            if ($test->properties->Platform_Maker === $companyData->name) {
//                $test->properties->Platform_Maker = $companyKey;
//            }
//
//            if ($test->properties->Device_Maker === $companyData->name) {
//                $test->properties->Device_Maker = $companyKey;
//            }
//
//            if ($test->properties->Device_Brand_Name === $companyData->brandname) {
//                $test->properties->Device_Brand_Name = $companyKey;
//            }
//        }
//
//        foreach ($browserTypes as $typeKey => $typeDate) {
//            if ($test->properties->Browser_Type === $typeDate->name) {
//                $test->properties->Browser_Type = $typeKey;
//                break;
//            }
//        }
//
//        foreach ($deviceTypes as $typeKey => $typeDate) {
//            if (str_replace('UaDeviceType\\', '', $test->properties->Device_Type) === $typeDate->name) {
//                $test->properties->Device_Type = $typeKey;
//                break;
//            }
//        }
//    }
//
//    file_put_contents(
//        $file->getPathname(),
//        json_encode($tests, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
//    );
//}

foreach (new \RecursiveIteratorIterator($iterator) as $file) {
    /** @var $file \SplFileInfo */
    if (!$file->isFile() || $file->getExtension() !== 'json') {
        continue;
    }

    echo 'handling ',  $file->getPathname(), ' ...', PHP_EOL;

    $tests          = json_decode(file_get_contents($file->getPathname()));
    $convertedTests = [];

    foreach ($tests as $key => $test) {
        $request = (new \Wurfl\Request\GenericRequestFactory())->createRequestForUserAgent($test->ua);

        $engine = new \UaResult\Engine\Engine(
            $test->properties->RenderingEngine_Name,
            (new \BrowserDetector\Loader\CompanyLoader($cache))->load($test->properties->RenderingEngine_Maker),
            (new \BrowserDetector\Version\VersionFactory())->set($test->properties->RenderingEngine_Version)
        );

        $browser = new \UaResult\Browser\Browser(
            $test->properties->Browser_Name,
            (new \BrowserDetector\Loader\CompanyLoader($cache))->load($test->properties->Browser_Maker),
            (new \BrowserDetector\Version\VersionFactory())->set($test->properties->Browser_Version),
            $engine,
            (new \UaBrowserType\TypeLoader($cache))->load($test->properties->Browser_Type),
            $test->properties->Browser_Bits,
            false,
            false,
            $test->properties->Browser_Modus
        );

        $platform = new \UaResult\Os\Os(
            $test->properties->Platform_Codename,
            $test->properties->Platform_Marketingname,
            (new \BrowserDetector\Loader\CompanyLoader($cache))->load($test->properties->Platform_Maker),
            (new \BrowserDetector\Version\VersionFactory())->set($test->properties->Platform_Version),
            $test->properties->Platform_Bits
        );

        $device = new \UaResult\Device\Device(
            $test->properties->Device_Code_Name,
            $test->properties->Device_Name,
            (new \BrowserDetector\Loader\CompanyLoader($cache))->load($test->properties->Device_Maker),
            (new \BrowserDetector\Loader\CompanyLoader($cache))->load($test->properties->Device_Brand_Name),
            null,
            $platform,
            (new \UaDeviceType\TypeLoader($cache))->load($test->properties->Device_Type),
            $test->properties->Device_Pointing_Method,
            null,
            null,
            $test->properties->Device_Dual_Orientation
        );

        $result = new \UaResult\Result\Result($request, $device, $platform, $browser, $engine);

        $convertedTests[$key] = [
            'ua'     => $test->ua,
            'result' => $result->toArray(),
        ];
    }

    file_put_contents(
        $file->getPathname(),
        json_encode($convertedTests, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
    );
}