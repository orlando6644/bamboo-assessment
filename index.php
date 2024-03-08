<?php

function callEndpoint($url, $method = 'GET')
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

class Travel
{
    private $travel_endpoint = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';

    public function getTravels(): array
    {
        try {
            return json_decode(callEndpoint($this->travel_endpoint), true);
        } catch (\Throwable $th) {
            return ['error' => 'An error occurred while fetching travels. Please try again later.'];
        }
    }
}
class Company
{
    private $company_endpoint = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';

    public function getCompanies(): array
    {
       try {
            return json_decode(callEndpoint($this->company_endpoint), true);
        } catch (\Throwable $th) {
            return ['error' => 'An error occurred while fetching companies. Please try again later.'];
        }
    }

    public function getCompaniesTravelTree()
    {
        try {
            $travel = new Travel();
            $travels = $travel->getTravels();
            $companies = $this->getCompanies();
    
            $companiesByUuId = [];
    
            foreach ($companies as $company) {
                $company['cost'] = 0;
                $company['children'] = [];
                $companiesByUuId[$company['id']] = $company;
            }
        
            foreach ($travels as $travel) {
                $companyUiId = $travel['companyId'];
                $cost = $travel['price'];
                while ($companyUiId !== "0") {
                    $companiesByUuId[$companyUiId]['cost'] += $cost;
                    $companyUiId = $companiesByUuId[$companyUiId]['parentId'];
                }
            }
        
            return $this->buildTree($companiesByUuId, "0");
        } catch (\Throwable $th) {
            return ['error' => 'An error occurred while fetching companies travel tree. Please try again later.' . $th->getMessage()];
        }
    }

    private function buildTree(&$companies, $parentId)
    {
        $tree = [];
        foreach ($companies as $company) {
            if ($company['parentId'] === $parentId) {
                $company['children'] = $this->buildTree($companies, $company['id']);
                $tree[] = $company;
            }
        }
        return $tree;
    }

}
class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        $company = new Company();
        echo json_encode($company->getCompaniesTravelTree(), JSON_PRETTY_PRINT);
        echo 'Total time: '.  (microtime(true) - $start);
    }
}

(new TestScript())->execute();