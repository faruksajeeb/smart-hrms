<?php

namespace Database\Seeders;

use App\Models\MasterDataItem;
use Illuminate\Database\Seeder;

class DummyMasterDataItemSeeder extends Seeder
{
    /**
     * Seed demo master data for HR lookups and org structure.
     */
    public function run(): void
    {
        $company = $this->upsert(
            MasterDataItem::CATEGORY_COMPANY,
            'ACME',
            'Acme Group',
            description: 'Primary group company for demo data.',
            sortOrder: 1,
        );

        $branchDhaka = $this->upsert(
            MasterDataItem::CATEGORY_BRANCH,
            'DHK',
            'Dhaka Branch',
            parentId: $company->id,
            sortOrder: 1,
        );

        $branchChittagong = $this->upsert(
            MasterDataItem::CATEGORY_BRANCH,
            'CTG',
            'Chittagong Branch',
            parentId: $company->id,
            sortOrder: 2,
        );

        $divisionOps = $this->upsert(
            MasterDataItem::CATEGORY_DIVISION,
            'OPS',
            'Operations Division',
            parentId: $branchDhaka->id,
            sortOrder: 1,
        );

        $divisionHr = $this->upsert(
            MasterDataItem::CATEGORY_DIVISION,
            'HRD',
            'Human Resources Division',
            parentId: $branchDhaka->id,
            sortOrder: 2,
        );

        $departmentPayroll = $this->upsert(
            MasterDataItem::CATEGORY_DEPARTMENT,
            'PAY',
            'Payroll Department',
            parentId: $divisionHr->id,
            sortOrder: 1,
        );

        $departmentRecruitment = $this->upsert(
            MasterDataItem::CATEGORY_DEPARTMENT,
            'REC',
            'Recruitment Department',
            parentId: $divisionHr->id,
            sortOrder: 2,
        );

        $departmentProduction = $this->upsert(
            MasterDataItem::CATEGORY_DEPARTMENT,
            'PROD',
            'Production Department',
            parentId: $divisionOps->id,
            sortOrder: 1,
        );

        foreach ([
            ['MGR', 'Manager', $departmentPayroll->id, 1],
            ['SR-HR', 'Senior HR Officer', $departmentRecruitment->id, 1],
            ['HR-OFF', 'HR Officer', $departmentRecruitment->id, 2],
            ['PROD-LEAD', 'Production Lead', $departmentProduction->id, 1],
        ] as [$code, $name, $parentId, $sortOrder]) {
            $this->upsert(
                MasterDataItem::CATEGORY_DESIGNATION,
                $code,
                $name,
                parentId: $parentId,
                sortOrder: $sortOrder,
            );
        }

        $districtDhaka = $this->upsert(
            MasterDataItem::CATEGORY_DISTRICT,
            'DHK-DIST',
            'Dhaka',
            sortOrder: 1,
        );

        $districtChittagong = $this->upsert(
            MasterDataItem::CATEGORY_DISTRICT,
            'CTG-DIST',
            'Chittagong',
            sortOrder: 2,
        );

        foreach ([
            ['DHK-CITY', 'Dhaka City', $districtDhaka->id, 1],
            ['GUL', 'Gulshan', $districtDhaka->id, 2],
            ['CTG-CITY', 'Chittagong City', $districtChittagong->id, 1],
        ] as [$code, $name, $parentId, $sortOrder]) {
            $this->upsert(
                MasterDataItem::CATEGORY_CITY,
                $code,
                $name,
                parentId: $parentId,
                sortOrder: $sortOrder,
            );
        }

        foreach ([
            ['BRAC', 'BRAC Bank'],
            ['DBBL', 'Dutch-Bangla Bank'],
            ['CITY', 'City Bank'],
            ['EBL', 'Eastern Bank'],
        ] as $index => [$code, $name]) {
            $this->upsert(
                MasterDataItem::CATEGORY_BANK,
                $code,
                $name,
                sortOrder: $index + 1,
            );
        }

        foreach ([
            MasterDataItem::CATEGORY_RELIGION => [
                ['ISL', 'Islam'],
                ['HIN', 'Hinduism'],
                ['BUD', 'Buddhism'],
                ['CHR', 'Christianity'],
            ],
            MasterDataItem::CATEGORY_BLOOD_GROUP => [
                ['A+', 'A+'],
                ['A-', 'A-'],
                ['B+', 'B+'],
                ['B-', 'B-'],
                ['O+', 'O+'],
                ['O-', 'O-'],
                ['AB+', 'AB+'],
                ['AB-', 'AB-'],
            ],
            MasterDataItem::CATEGORY_MARITAL_STATUS => [
                ['SGL', 'Single'],
                ['MRD', 'Married'],
                ['DIV', 'Divorced'],
                ['WID', 'Widowed'],
            ],
            MasterDataItem::CATEGORY_EMPLOYEE_TYPE => [
                ['PERM', 'Permanent'],
                ['CONT', 'Contractual'],
                ['PROB', 'Probationary'],
                ['INT', 'Intern'],
            ],
            MasterDataItem::CATEGORY_QUALIFICATION => [
                ['SSC', 'SSC'],
                ['HSC', 'HSC'],
                ['BSC', 'Bachelor Degree'],
                ['MSC', 'Master Degree'],
                ['MBA', 'MBA'],
            ],
            MasterDataItem::CATEGORY_PAY_TYPE => [
                ['MON', 'Monthly'],
                ['WK', 'Weekly'],
                ['DLY', 'Daily'],
                ['HR', 'Hourly'],
            ],
            MasterDataItem::CATEGORY_RELATIVE => [
                ['FTH', 'Father'],
                ['MTH', 'Mother'],
                ['SPO', 'Spouse'],
                ['SIB', 'Sibling'],
                ['CHD', 'Child'],
            ],
            MasterDataItem::CATEGORY_JOB_GRADE => [
                ['G1', 'Grade 1'],
                ['G2', 'Grade 2'],
                ['G3', 'Grade 3'],
                ['G4', 'Grade 4'],
                ['G5', 'Grade 5'],
            ],
            MasterDataItem::CATEGORY_LEAVE_TYPE => [
                ['AL', 'Annual Leave'],
                ['SL', 'Sick Leave'],
                ['CL', 'Casual Leave'],
                ['ML', 'Maternity Leave'],
                ['UL', 'Unpaid Leave'],
            ],
        ] as $category => $items) {
            foreach ($items as $index => [$code, $name]) {
                $this->upsert(
                    $category,
                    $code,
                    $name,
                    sortOrder: $index + 1,
                );
            }
        }
    }

    protected function upsert(
        string $category,
        string $code,
        string $name,
        ?int $parentId = null,
        ?string $description = null,
        int $sortOrder = 0,
    ): MasterDataItem {
        return MasterDataItem::query()->updateOrCreate(
            [
                'category' => $category,
                'code' => $code,
            ],
            [
                'parent_id' => $parentId,
                'name' => $name,
                'description' => $description,
                'status' => MasterDataItem::STATUS_ACTIVE,
                'sort_order' => $sortOrder,
            ],
        );
    }
}
