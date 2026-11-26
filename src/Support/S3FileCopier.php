<?php

namespace Spatie\MediaLibrary\Support;

use Aws\CommandPool;
use Aws\S3\S3Client;
use Illuminate\Filesystem\AwsS3V3Adapter;
use League\Flysystem\AwsS3V3\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Throwable;

class S3FileCopier
{
    /**
     * @param  array<string, string>  $destinationsByFile
     * @return array<int, string>
     */
    public function copy(AwsS3V3Adapter $disk, array $destinationsByFile, ?string $acl = null): array
    {
        $client = $disk->getClient();
        $bucket = $disk->getConfig()['bucket'];
        $acl ??= (new PortableVisibilityConverter)->visibilityToAcl($disk->getConfig()['visibility'] ?? Visibility::PRIVATE);

        $files = array_keys($destinationsByFile);

        $commands = array_map(fn (string $file) => $client->getCommand('CopyObject', [
            'Bucket' => $bucket,
            'Key' => $disk->path($destinationsByFile[$file]),
            'CopySource' => $bucket.'/'.S3Client::encodeKey($disk->path($file)),
            'ACL' => $acl,
        ]), $files);

        $results = CommandPool::batch($client, $commands);

        $failedFiles = array_filter($files, fn (int $index) => $results[$index] instanceof Throwable, ARRAY_FILTER_USE_KEY);

        return array_values($failedFiles);
    }
}
