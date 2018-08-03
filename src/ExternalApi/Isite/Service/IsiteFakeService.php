<?php
namespace App\ExternalApi\Isite\Service;

use App\Builders\IsiteResultBuilder;
use App\Builders\ProfileBuilder;
use App\ExternalApi\Isite\IsiteResult;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class IsiteFakeService extends IsiteService
{
    public function getByContentId(string $guid, bool $preview = false): PromiseInterface
    {
        if ($guid == '12873a63-112a-41f9-b1a2-2ea6348083c0') {
            $profileA = ProfileBuilder::anyGroup()->with([
                'parentPid' => new Pid('p3000000'),
                'projectSpace' => 'progs-radio4and4extra',
                'title' => 'men-minutes-presenters',
                'key' => '4YDBGmJwZTYtGTk2PCCbsXw',
            ])->build();
            $profileA->setChildren([ProfileBuilder::any()->build()]);
            $profileB = ProfileBuilder::anyGroup()->with([
                'parentPid' => new Pid('p3000001'),
                'projectSpace' => 'progs-radio4and4extra',
                'title' => 'men-minutes-presenters',
                'key' => '4YDBGmJwZTYtGTk2PCCbsXw',
            ])->build();
            $profileB->setChildren([ProfileBuilder::any()->build()]);

            // Fixture: isite return two group profiles
            $isiteResult = new IsiteResult(
                1,
                10,
                2,
                [
                    $profileA,
                    $profileB,
                ]
            );
        } else if ($guid == 'd36f61e3-989c-34e8-2f03-ca6348083c0f') {
            // Fixture: isite return two individual profiles
            $isiteResult = new IsiteResult(
                1,
                10,
                2,
                [
                    ProfileBuilder::anyIndividual()->with([
                        'parentPid' => new Pid('p3000000'),
                        'projectSpace' => 'progs-radio4and4extra',
                        'title' => 'men-minutes-presenters',
                        'key' => 'profile1key',
                    ])->build(),
                    ProfileBuilder::anyIndividual()->with([
                        'parentPid' => new Pid('p3000001'),
                        'projectSpace' => 'progs-radio4and4extra',
                        'title' => 'men-minutes-presenters',
                        'key' => 'profile2key',
                    ])->build(),
                ]
            );
        } else {
            // Fixture: isite doesnt return profiles
            $isiteResult = new IsiteResult(1, 10, 0, []);
        }

        return new FulfilledPromise($isiteResult);
    }

    public function setChildProfilesOn(
        array $profiles,
        string $project,
        int $page = 1,
        int $limit = 48
    ): PromiseInterface {
        return new FulfilledPromise([new IsiteResult(1, 1, 0, [])]);
    }
}
