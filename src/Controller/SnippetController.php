<?php
declare(strict_types = 1);
namespace App\Controller;

use App\ExternalApi\Tupac\Service\TupacService;
use Symfony\Component\HttpFoundation\Request;

class SnippetController extends BaseController
{
    public function __invoke(Request $request, string $recordsIds, TupacService $tupacService)
    {
        $recordsIdsArray = $this->getRecordsIdsAsArray($recordsIds);
        $records = [];
        if (!empty($recordsIdsArray)) {
            // check if is UK IP
            $isUk = ($request->headers->get('X-Ip_is_uk_combined', 'no') === 'yes');
            $records = $tupacService->fetchRecordsByIds($recordsIdsArray, $isUk)->wait(true);
        }

        $responseArray = [];
        foreach ($records as $record) {
            $responseArray[] = [
                'id' => $record->getRecordId(),
                'html' => $this->container->get('twig')->render('snippet/snippet_record.html.twig', ['record' => $record]),
            ];
        }

        $this->response()->headers->set('content-type', 'application/json');
        $this->response()->setVary(['X-Ip_is_uk_combined'], false);
        $this->response()->setContent(json_encode($responseArray));
        return $this->response();
    }

    private function getRecordsIdsAsArray(string $recordsIds): array
    {
        $recordsIdsArray = explode(',', $recordsIds);
        foreach ($recordsIdsArray as $key => $value) {
            // remove invalid recordsIds, there are only 23 valid characters and length should always be 6
            if (!preg_match('/^[qg429wfmrhxbznjpc3568dv]{6}$/', $value)) {
                unset($recordsIdsArray[$key]);
            }
        }
        return $recordsIdsArray;
    }
}
