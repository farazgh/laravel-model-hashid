<?php

declare(strict_types=1);

namespace Deligoez\LaravelModelHashIDs\Tests\Traits;

use Str;
use Illuminate\Foundation\Testing\WithFaker;
use Deligoez\LaravelModelHashIDs\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Deligoez\LaravelModelHashIDs\Tests\Models\ModelA;
use Deligoez\LaravelModelHashIDs\Support\HashIDModelConfig;
use Deligoez\LaravelModelHashIDs\Support\ModelHashIDGenerator;

class HasHasIDTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    // region Trait Initialization

    /** @test */
    public function model_hashID_salt_can_be_defined(): void
    {
        // 1️⃣ Arrange 🏗
        $model = ModelA::factory()->create();
        $hash = $model->hashID;

        // 2️⃣ Act 🏋🏻‍
        HashIDModelConfig::set(HashIDModelConfig::SALT, Str::random());

        // 3️⃣ Assert ✅
        $newHash = ModelA::findOrFail($model->getKey())->hashID;
        $this->assertNotEquals($hash, $newHash);
    }

    /** @test */
    public function model_hashID_length_can_be_defined(): void
    {
        // 1️⃣ Arrange 🏗
        $randomLength = $this->faker->numberBetween(5, 20);
        HashIDModelConfig::set(HashIDModelConfig::LENGTH, $randomLength);

        $model = ModelA::factory()->create();

        // 2️⃣ Act 🏋🏻‍
        $hashID = $model->hashID;

        // 3️⃣ Assert ✅
        $length = mb_strlen(HashIDModelConfig::get(HashIDModelConfig::SEPARATOR)) +
            HashIDModelConfig::get(HashIDModelConfig::PREFIX_LENGTH) +
            $randomLength;

        $this->assertEquals($length, mb_strlen($hashID));
    }

    /** @test */
    public function model_hashID_alphabet_can_be_defined(): void
    {
        // 1️⃣ Arrange 🏗
        $customAlphabet = 'abcdef1234567890';
        HashIDModelConfig::set(HashIDModelConfig::ALPHABET, $customAlphabet);

        $model = ModelA::factory()->create();

        // 2️⃣ Act 🏋🏻‍
        $hashID = $model->hashID;

        // 3️⃣ Assert ✅
        $modelHashID = ModelHashIDGenerator::parseHashIDForModel($hashID);

        $alphabetAsArray = mb_str_split($customAlphabet);
        foreach (mb_str_split($modelHashID->hashIDForKey) as $char) {
            $this->assertContains($char, $alphabetAsArray);
        }
    }

    // endregion

    // region Trait Static Functions

    /** @test */
    public function it_can_get_a_model_key_from_hashID(): void
    {
        // 1️⃣ Arrange 🏗
        $model = ModelA::factory()->create();
        $hashID = $model->hashID;

        // 2️⃣ Act 🏋🏻‍
        $key = ModelA::keyFromHashID($hashID);

        // 3️⃣ Assert ✅
        $this->assertEquals($model->getKey(), $key);
    }

    /** @test */
    public function it_returns_null_if_hashID_can_not_parsable(): void
    {
        // 2️⃣ Act 🏋🏻‍
        $key = ModelA::keyFromHashID('non-existing-hash-id');

        // 3️⃣ Assert ✅
        $this->assertNull($key);
    }

    // endregion

    // region Accessors

    /** @test */
    public function model_has_a_hashID_attribute(): void
    {
        // 1️⃣ Arrange 🏗
        $model = ModelA::factory()->create();

        // 2️⃣ Act 🏋🏻‍
        $hashID = $model->hashID;
        $key = $model->getKey();

        // 3️⃣ Assert ✅
        $this->assertEquals($key, ModelA::keyFromHashID($hashID));
    }

    /** @test */
    public function model_has_a_hashIDRaw_attribute(): void
    {
        // 1️⃣ Arrange 🏗
        $model = ModelA::factory()->create();

        // 2️⃣ Act 🏋🏻‍
        $hashIDRawAttribute = $model->hashIDRaw;

        // 3️⃣ Assert ✅
        $hashIDRaw = ModelHashIDGenerator::parseHashIDForModel($model->hashID)->hashIDForKey;
        $this->assertEquals($hashIDRaw, $hashIDRawAttribute);
    }

    // endregion
}
