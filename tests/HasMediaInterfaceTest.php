<?php

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Methods that are internal to the package or that require Media Library Pro,
 * and therefore are intentionally absent from the public contract.
 */
function methodsAbsentFromContractOnPurpose(): array
{
    return [
        '__sleep',
        'addFromMediaLibraryRequest',
        'bootInteractsWithMedia',
        'getMediaRepository',
        'processUnattachedMedia',
        'syncFromMediaLibraryRequest',
    ];
}

function publicMethodsOfInteractsWithMedia(): array
{
    $methods = (new ReflectionClass(InteractsWithMedia::class))->getMethods(ReflectionMethod::IS_PUBLIC);

    return collect($methods)
        ->map(fn (ReflectionMethod $method) => $method->getName())
        ->reject(fn (string $name) => in_array($name, methodsAbsentFromContractOnPurpose(), true))
        ->values()
        ->all();
}

function methodsDocumentedOnHasMedia(): array
{
    $reflection = new ReflectionClass(HasMedia::class);

    $declared = collect($reflection->getMethods())
        ->map(fn (ReflectionMethod $method) => $method->getName());

    preg_match_all('/@method\s+[^\s]+\s+(\w+)\(/', $reflection->getDocComment() ?: '', $matches);

    return $declared->merge($matches[1])->unique()->all();
}

it('describes every public method of the trait', function () {
    $undocumented = array_diff(publicMethodsOfInteractsWithMedia(), methodsDocumentedOnHasMedia());

    expect($undocumented)->toBeEmpty(
        'These methods exist on InteractsWithMedia but are neither declared nor documented on HasMedia: '
        .implode(', ', $undocumented)
    );
});

it('does not document methods that the trait does not provide', function () {
    $reflection = new ReflectionClass(HasMedia::class);

    preg_match_all('/@method\s+[^\s]+\s+(\w+)\(/', $reflection->getDocComment() ?: '', $matches);

    $traitMethods = collect((new ReflectionClass(InteractsWithMedia::class))->getMethods())
        ->map(fn (ReflectionMethod $method) => $method->getName())
        ->all();

    expect(array_diff($matches[1], $traitMethods))->toBeEmpty();
});
