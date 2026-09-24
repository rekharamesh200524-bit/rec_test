<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * JobContentGenerator Library
 * 
 * Universal Multi-Department Occupation-Based Engine for CodeIgniter HRMS / Recruitment Portal.
 * Converts skills into natural, occupation-specific action duties while eliminating generic placeholders
 * (e.g. "Apply technical expertise in X to execute core duties").
 */
class JobContentGenerator {

    protected $ci;

    public function __construct() {
        $this->ci = &get_instance();
    }

    /**
     * Main entry point for universal occupation-based composition.
     * 
     * @param array $data Input vacancy attributes
     * @return array Standardized response array with status, job_description, and responsibilities
     */
    public function generate($data = []) {
        $normalized = $this->normalizeInput($data);

        $rawTitle        = $normalized['JobTitle'];
        $rawRole         = $normalized['FunctionalRole'];
        $rawDept         = $normalized['Department'];
        $expMin          = $normalized['ExpMin'];
        $expMax          = $normalized['ExpMax'];
        $mustSkills      = $normalized['MustHaveSkills'];
        $niceSkills      = $normalized['NiceToHaveSkills'];
        $location        = $normalized['JobLocation'];
        $communication   = $normalized['CommunicationLang'];

        if (empty($rawTitle) && empty($rawRole) && empty($rawDept) && empty($mustSkills) && empty($niceSkills)) {
            return [
                'status'  => 'error',
                'message' => 'Please enter a Job Title, Role, Department, or Skills before generating.'
            ];
        }

        // 1. Detect Seniority
        $seniority = $this->detectSeniority($rawTitle . ' ' . $rawRole);

        // 2. Detect Occupation Profile (Universal Priority: JobTitle > Dept > MustSkills > FunctionalRole > NiceSkills)
        $profile = $this->detectOccupation($rawTitle, $rawRole, $rawDept, $mustSkills, $niceSkills);

        $expText = $this->getExperienceText($expMin, $expMax);

        // 3. Classify Skills (Technical/Domain vs Soft Skills)
        $classifiedMust = $this->classifySkills($mustSkills);
        $classifiedNice = $this->classifySkills($niceSkills);

        // 4. Compose Job Description
        $jobDescription = $this->composeJobDescription(
            $profile,
            $seniority,
            $expText,
            $classifiedMust,
            $classifiedNice,
            $location,
            $communication,
            $rawTitle,
            $rawRole
        );

        // 5. Compose Roles & Responsibilities
        $responsibilities = $this->composeResponsibilities(
            $profile,
            $seniority,
            $classifiedMust,
            $classifiedNice
        );

        return [
            'status'           => 'success',
            'suggested_title'  => $profile['name'],
            'job_description'  => $jobDescription,
            'responsibilities' => $responsibilities
        ];
    }

    /**
     * Normalize all input attributes
     */
    private function normalizeInput($data) {
        $jobTitle       = isset($data['JobTitle']) ? trim($data['JobTitle']) : '';
        $functionalRole = isset($data['FunctionalRole']) ? trim($data['FunctionalRole']) : '';
        $department     = isset($data['Department']) ? trim($data['Department']) : '';
        $expMin         = isset($data['ExpMin']) ? (float)$data['ExpMin'] : 0;
        $expMax         = isset($data['ExpMax']) ? (float)$data['ExpMax'] : 0;
        $location       = isset($data['JobLocation']) ? trim($data['JobLocation']) : '';
        $commLang       = isset($data['CommunicationLang']) ? trim($data['CommunicationLang']) : '';

        $mustSkillsStr  = isset($data['MustHaveSkills']) ? $data['MustHaveSkills'] : '';
        $niceSkillsStr  = isset($data['NiceToHaveSkills']) ? $data['NiceToHaveSkills'] : '';

        return [
            'JobTitle'          => $jobTitle,
            'FunctionalRole'    => $functionalRole,
            'Department'        => $department,
            'ExpMin'            => $expMin,
            'ExpMax'            => $expMax,
            'MustHaveSkills'    => $this->normalizeSkills($mustSkillsStr),
            'NiceToHaveSkills'  => $this->normalizeSkills($niceSkillsStr),
            'JobLocation'       => $location,
            'CommunicationLang' => $commLang
        ];
    }

    /**
     * Normalizes skill input string into unique array of clean skill titles.
     */
    private function normalizeSkills($skillInput) {
        if (is_array($skillInput)) {
            $skills = $skillInput;
        } else {
            $cleanStr = str_replace(["\r\n", "\n", "\r", "|", ";"], ',', (string)$skillInput);
            $skills   = explode(',', $cleanStr);
        }

        $result = [];
        foreach ($skills as $s) {
            $trimmed = trim($s);
            if ($trimmed !== '') {
                $lower = strtolower($trimmed);
                if (!isset($result[$lower])) {
                    $result[$lower] = $trimmed;
                }
            }
        }

        return array_values($result);
    }

    /**
     * Detect Seniority Level independently
     */
    private function detectSeniority($text) {
        $lower = strtolower($text);

        if (preg_match('/\b(intern|trainee|fresher)\b/', $lower)) {
            return 'Intern';
        }
        if (preg_match('/\b(junior|jr|assistant)\b/', $lower)) {
            return 'Junior';
        }
        if (preg_match('/\b(senior|sr|principal|staff|architect)\b/', $lower)) {
            return 'Senior';
        }
        if (preg_match('/\b(lead|team lead|tech lead)\b/', $lower)) {
            return 'Lead';
        }
        if (preg_match('/\b(manager|head|director|vp)\b/', $lower)) {
            return 'Manager';
        }

        return 'Mid';
    }

    /**
     * STEP 9: Classifies skills into Technical/Domain vs Soft Skills
     */
    private function classifySkills($skillsArray) {
        $softSkillList = [
            'communication', 'problem solving', 'team collaboration', 'teamwork', 'interpersonal skills',
            'time management', 'adaptability', 'critical thinking', 'leadership', 'work ethic', 'multitasking',
            'attention to detail', 'collaboration', 'negotiation', 'analytical skills', 'conflict resolution'
        ];

        $technical = [];
        $soft = [];

        foreach ($skillsArray as $skill) {
            $lower = strtolower(trim($skill));
            if (in_array($lower, $softSkillList)) {
                $soft[] = $skill;
            } else {
                $technical[] = $skill;
            }
        }

        return [
            'technical' => $technical,
            'soft'      => $soft
        ];
    }

    /**
     * Detect Occupation Profile across all departments
     */
    private function detectOccupation($jobTitle, $functionalRole, $department, $mustSkills, $niceSkills) {
        $profiles = $this->getOccupationProfiles();

        // 1. Try Job Title Exact / Alias / Keyword match
        if (!empty($jobTitle)) {
            $matched = $this->matchOccupationText($jobTitle, $profiles);
            if ($matched !== null) return $matched;
        }

        // 2. Try Department match
        if (!empty($department)) {
            $matched = $this->matchOccupationText($department, $profiles);
            if ($matched !== null) return $matched;
        }

        // 3. Try Must-Have Skills match
        $mustStr = is_array($mustSkills) ? implode(' ', $mustSkills) : (string)$mustSkills;
        if (!empty($mustStr)) {
            $matched = $this->matchOccupationText($mustStr, $profiles);
            if ($matched !== null) return $matched;
        }

        // 4. Try Functional Role match (excluding standalone 'Executive')
        if (!empty($functionalRole) && strtolower(trim($functionalRole)) !== 'executive') {
            $matched = $this->matchOccupationText($functionalRole, $profiles);
            if ($matched !== null) return $matched;
        }

        // 5. Try Nice-To-Have Skills match
        $niceStr = is_array($niceSkills) ? implode(' ', $niceSkills) : (string)$niceSkills;
        if (!empty($niceStr)) {
            $matched = $this->matchOccupationText($niceStr, $profiles);
            if ($matched !== null) return $matched;
        }

        // 6. Neutral General Fallback
        return $this->getNeutralFallbackProfile($jobTitle, $functionalRole, $department);
    }

    /**
     * Matches text against occupation aliases and keywords
     */
    private function matchOccupationText($text, $profiles) {
        $lower = strtolower($text);

        foreach ($profiles as $key => $p) {
            // Alias match
            foreach ($p['aliases'] as $alias) {
                if (preg_match('/\b' . preg_quote(strtolower($alias), '/') . '\b/i', $lower)) {
                    return $p;
                }
            }
            // Keyword match
            foreach ($p['keywords'] as $kw) {
                if (preg_match('/\b' . preg_quote(strtolower($kw), '/') . '\b/i', $lower)) {
                    return $p;
                }
            }
        }

        return null;
    }

    /**
     * Neutral Fallback Profile for Unknown / Unlisted Occupations
     */
    private function getNeutralFallbackProfile($jobTitle, $functionalRole, $department) {
        $titleToUse = !empty($jobTitle) ? $jobTitle : (!empty($functionalRole) ? $functionalRole : 'Specialist');
        $deptToUse  = !empty($department) ? $department : 'Operational';

        return [
            'name'                  => $titleToUse,
            'domain'                => $deptToUse,
            'aliases'               => [],
            'keywords'              => [],
            'purpose'               => "executing key responsibilities, maintaining operational quality, and supporting workflow deliverables within {$deptToUse}",
            'base_responsibilities' => [
                "Execute assigned functional duties in accordance with operational standards.",
                "Monitor, maintain, and report on task quality, accuracy, and workflow efficiency.",
                "Identify process bottlenecks and implement practical operational solutions.",
                "Collaborate with team members and internal stakeholders to achieve organizational objectives."
            ],
            'blocked_terms'         => []
        ];
    }

    /**
     * Experience Text
     */
    private function getExperienceText($expMin, $expMax) {
        if ($expMin > 0 && $expMax > 0) {
            if ($expMin == $expMax) {
                return "with $expMin years of relevant experience";
            }
            return "with {$expMin}–{$expMax} years of relevant experience";
        } elseif ($expMin > 0) {
            return "with at least $expMin years of relevant experience";
        } elseif ($expMax > 0) {
            return "with up to $expMax years of relevant experience";
        }
        return "";
    }

    /**
     * Job Description Composition Engine
     */
    private function composeJobDescription($profile, $seniority, $expText, $classifiedMust, $classifiedNice, $location, $communication, $rawTitle, $rawRole) {
        $displayTitle = !empty($rawTitle) ? $rawTitle : $profile['name'];
        $prefix       = in_array(strtolower(substr($seniority, 0, 1)), ['a', 'e', 'i', 'o', 'u']) ? 'an' : 'a';

        // Opening sentence
        $intro = "We are looking for {$prefix} ";
        if ($seniority !== 'Mid' && stripos($displayTitle, $seniority) === false) {
            $intro .= "{$seniority} {$displayTitle}";
        } else {
            $intro .= "{$displayTitle}";
        }

        if (!empty($rawRole) && stripos($displayTitle, $rawRole) === false && strtolower($rawRole) !== 'executive') {
            $intro .= " ({$rawRole})";
        }
        $intro .= " to join our team.";

        // Purpose sentence
        $purposeText = "The candidate will be responsible for " . $profile['purpose'] . ".";

        // Must-Have Technical/Domain Skills sentence
        $mustTech = $classifiedMust['technical'];
        $mustText = "";
        if (!empty($mustTech)) {
            $topMust = array_slice($mustTech, 0, 6);
            $mustText = "The role will involve working extensively with " . $this->formatList($topMust) . " to deliver reliable and quality outcomes.";
        }

        // Nice-To-Have Technical/Domain Skills sentence
        $niceTech = $classifiedNice['technical'];
        $niceText = "";
        if (!empty($niceTech)) {
            $topNice = array_slice($niceTech, 0, 3);
            $niceText = "Familiarity with " . $this->formatList($topNice) . " will be an added advantage.";
        }

        // Experience & Location context
        $extraContext = [];
        if (!empty($expText)) {
            $extraContext[] = $expText;
        }
        if (!empty($location)) {
            $extraContext[] = "based in {$location}";
        }
        if (!empty($communication)) {
            $extraContext[] = "requiring strong communication skills in {$communication}";
        }

        $extraStr = "";
        if (!empty($extraContext)) {
            $extraStr = "This is a full-time role " . implode(", ", $extraContext) . ".";
        }

        // Natural Soft Skills integration into Closing sentence
        $allSoft = array_merge($classifiedMust['soft'], $classifiedNice['soft']);
        $closing = "The ideal candidate should be a proactive problem solver with a strong commitment to quality and team collaboration.";
        if (!empty($allSoft)) {
            $softFormatted = $this->formatList(array_unique($allSoft));
            $closing = "The ideal candidate should demonstrate strong " . strtolower($softFormatted) . " skills and maintain a strong commitment to quality and team collaboration.";
        }

        return trim("{$intro} {$purposeText} {$mustText} {$niceText} {$extraStr} {$closing}");
    }

    /**
     * Responsibilities Composition Engine
     */
    private function composeResponsibilities($profile, $seniority, $classifiedMust, $classifiedNice) {
        $candidateItems = [];

        // 1. Core Occupation Responsibilities adapted for Seniority
        foreach ($profile['base_responsibilities'] as $resp) {
            $candidateItems[] = $this->adaptResponsibilityForSeniority($resp, $seniority);
        }

        // 2. Technical / Domain Must-Have Skills Responsibilities (NATURAL ACTIONS, NO PLACEHOLDERS!)
        foreach ($classifiedMust['technical'] as $skill) {
            $actions = $this->generateSkillActions($skill, $profile, $seniority);
            foreach ($actions as $act) {
                $candidateItems[] = $this->adaptResponsibilityForSeniority($act, $seniority);
            }
        }

        // 3. Technical / Domain Nice-To-Have Skills Responsibilities (Optional Wording)
        foreach ($classifiedNice['technical'] as $skill) {
            $actions = $this->generateNiceToHaveSkillActions($skill, $profile);
            foreach ($actions as $act) {
                $candidateItems[] = $act;
            }
        }

        // 4. Seniority / Leadership Duty
        if ($seniority === 'Senior') {
            $candidateItems[] = "Mentor junior staff, participate in work reviews, and contribute to technical/operational improvements.";
        } elseif ($seniority === 'Lead' || $seniority === 'Manager') {
            $candidateItems[] = "Lead workflow planning, oversee operational timelines, and coordinate effectively across teams.";
        } else {
            $candidateItems[] = "Collaborate cross-functionally with team members to ensure seamless execution and timely delivery.";
        }

        // Validate each responsibility against Occupation Blocked Terms
        $validatedItems = [];
        foreach ($candidateItems as $item) {
            if ($this->validateResponsibilityOccupation($item, $profile)) {
                $validatedItems[] = $item;
            }
        }

        // Deduplicate & format as numbered list
        $cleanItems = $this->deduplicateResponsibilities($validatedItems);

        // Cap at 6 - 10 responsibilities
        if (count($cleanItems) > 10) {
            $cleanItems = array_slice($cleanItems, 0, 10);
        }

        $formatted = [];
        $index = 1;
        foreach ($cleanItems as $item) {
            $formatted[] = "{$index}. " . ucfirst(trim($item));
            $index++;
        }

        return implode("\n", $formatted);
    }

    /**
     * STEP 5, 6 & 11: Converts a skill into natural, occupation-aware action sentences (NO GENERIC PLACEHOLDERS!)
     */
    private function generateSkillActions($skill, $profile, $seniority) {
        $lowerSkill = strtolower(trim($skill));
        $domain     = isset($profile['domain']) ? $profile['domain'] : 'General';
        $skillMap   = $this->getSkillMappings();

        // 1. Exact or partial match in static skill dictionary
        if (isset($skillMap[$lowerSkill])) {
            $mapped = $skillMap[$lowerSkill];
            if (is_callable($mapped)) {
                return (array)$mapped($domain);
            }
            return (array)$mapped;
        }

        foreach ($skillMap as $key => $mapped) {
            if ($key !== '' && (strpos($lowerSkill, $key) !== false || strpos($key, $lowerSkill) !== false)) {
                if (is_callable($mapped)) {
                    return (array)$mapped($domain);
                }
                return (array)$mapped;
            }
        }

        // 2. Smart Occupation-Aware Fallback (NO "Apply technical expertise in X..."!)
        if ($domain === 'Sales') {
            return ["Leverage {$skill} to drive sales prospecting, customer engagement, and opportunity execution."];
        } elseif ($domain === 'Finance & Accounts') {
            return ["Incorporate {$skill} into financial calculations, accounting workflows, and statutory documentation."];
        } elseif ($domain === 'Human Resources') {
            return ["Utilize {$skill} to support candidate sourcing, employee engagement, and HR operations."];
        } elseif (in_array($domain, ['Technology', 'Software Development', 'Frontend Development', 'Backend Development', 'Data Engineering'])) {
            return ["Develop, test, and maintain application features and software components using {$skill}."];
        } elseif (in_array($domain, ['Security Systems', 'Electrical / Maintenance', 'HVAC / Maintenance'])) {
            return ["Utilize {$skill} during system installation, inspection, testing, and maintenance procedures."];
        } elseif ($domain === 'Healthcare') {
            return ["Apply {$skill} in clinical care workflows, patient monitoring, and medical documentation."];
        }

        return ["Incorporate {$skill} effectively to support operational workflows and project execution."];
    }

    /**
     * Generates optional wording for Nice-To-Have skills
     */
    private function generateNiceToHaveSkillActions($skill, $profile) {
        $lowerSkill = strtolower(trim($skill));
        if ($lowerSkill === 'sales methodology certification') {
            return ["Apply familiarity with established sales methodologies as an added advantage for pipeline growth."];
        } elseif ($lowerSkill === 'technical aptitude') {
            return ["Utilize technical aptitude as an added advantage when evaluating technology-driven solutions."];
        }
        return ["Utilize {$skill} as an added advantage for duty execution."];
    }

    /**
     * Validates that a responsibility string is consistent with the Occupation Profile and not matching blocked terms
     */
    private function validateResponsibilityOccupation($responsibility, $profile) {
        $lower = strtolower($responsibility);

        if (!empty($profile['blocked_terms'])) {
            foreach ($profile['blocked_terms'] as $term) {
                if (preg_match('/\b' . preg_quote(strtolower($term), '/') . '\b/i', $lower)) {
                    return false; // Reject responsibility
                }
            }
        }

        return true; // Approved
    }

    /**
     * Adapt Responsibility Action Verbs based on Seniority
     */
    private function adaptResponsibilityForSeniority($text, $seniority) {
        if ($seniority === 'Intern' || $seniority === 'Junior') {
            $replacements = [
                '/^Design and develop/i' => 'Assist in designing and developing',
                '/^Install/i'            => 'Assist in installing',
                '/^Lead/i'              => 'Support',
                '/^Manage/i'            => 'Assist in managing',
                '/^Architect/i'         => 'Help implement',
                '/^Drive/i'             => 'Contribute to',
                '/^Oversee/i'           => 'Assist in monitoring'
            ];
            return preg_replace(array_keys($replacements), array_values($replacements), $text);
        } elseif ($seniority === 'Senior') {
            $replacements = [
                '/^Assist in/i'          => 'Design and lead',
                '/^Support/i'            => 'Optimize and drive',
                '/^Help/i'               => 'Lead execution of'
            ];
            return preg_replace(array_keys($replacements), array_values($replacements), $text);
        } elseif ($seniority === 'Lead' || $seniority === 'Manager') {
            $replacements = [
                '/^Assist in/i'          => 'Oversee',
                '/^Support/i'            => 'Lead and manage',
                '/^Develop/i'            => 'Lead the development of'
            ];
            return preg_replace(array_keys($replacements), array_values($replacements), $text);
        }

        return $text;
    }

    /**
     * Deduplicates list of responsibilities.
     */
    private function deduplicateResponsibilities($items) {
        $seen = [];
        $result = [];

        foreach ($items as $item) {
            $clean = trim(preg_replace('/\s+/', ' ', $item));
            $key   = strtolower(substr($clean, 0, 35));

            if (!isset($seen[$key]) && strlen($clean) > 5) {
                $seen[$key] = true;
                $result[]   = $clean;
            }
        }

        return $result;
    }

    /**
     * Helper to format array as grammatically clean string ("A, B, and C").
     */
    private function formatList($array) {
        $count = count($array);
        if ($count === 0) return '';
        if ($count === 1) return $array[0];
        if ($count === 2) return $array[0] . ' and ' . $array[1];
        $last = array_pop($array);
        return implode(', ', $array) . ', and ' . $last;
    }

    /**
     * Master Expandable Occupation Profiles across all departments
     */
    private function getOccupationProfiles() {
        return [
            // ACCOUNT EXECUTIVE / SALES
            'account_executive' => [
                'name'     => 'Account Executive',
                'domain'   => 'Sales',
                'aliases'  => ['account executive', 'sales executive', 'sales representative', 'business development executive', 'sales account executive'],
                'keywords' => ['account executive', 'sales executive', 'sales', 'business development', 'b2b sales', 'lead generation', 'crm'],
                'purpose'  => 'driving revenue growth, identifying business opportunities, conducting discovery, and managing client relationships',
                'base_responsibilities' => [
                    'Identify and pursue new business opportunities through prospecting, outreach, and lead-generation activities.',
                    'Conduct discovery conversations to understand customer requirements, business challenges, and purchasing needs.',
                    'Conduct product and solution presentations based on customer requirements.',
                    'Build and maintain strong, long-lasting client relationships.',
                    'Track sales activities, pipeline progress, and assigned revenue targets.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'cctv', 'gst', 'nursing', 'medical']
            ],

            // SECURITY / CCTV
            'cctv_technician' => [
                'name'     => 'CCTV Technician',
                'domain'   => 'Security Systems',
                'aliases'  => ['cctv technician', 'cctv engineer', 'cctv installation technician', 'surveillance technician', 'security camera technician', 'cctv service technician'],
                'keywords' => ['cctv', 'surveillance', 'dvr', 'nvr', 'ip camera', 'security systems'],
                'purpose'  => 'installing, configuring, maintaining, and troubleshooting CCTV surveillance systems and related security equipment',
                'base_responsibilities' => [
                    'Install, configure, and maintain CCTV cameras, cabling, and mounting structures.',
                    'Configure DVR/NVR systems and verify proper video recording, storage, and remote access.',
                    'Perform comprehensive testing of surveillance systems to ensure proper camera coverage and clarity.',
                    'Troubleshoot camera, cabling, power supply, and network connectivity issues.',
                    'Perform preventive maintenance on surveillance equipment and maintain service logs.',
                    'Coordinate with clients and internal teams to resolve technical service requests.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'react', 'gst', 'sales targets', 'recruitment', 'payroll']
            ],

            // ELECTRICAL
            'electrician' => [
                'name'     => 'Electrician',
                'domain'   => 'Electrical / Maintenance',
                'aliases'  => ['electrician', 'electrical technician', 'electrical engineer', 'electrical maintenance technician', 'electrical supervisor'],
                'keywords' => ['electrical', 'electrician', 'wiring', 'switchgear', 'transformer', 'circuit breaker'],
                'purpose'  => 'installing, inspecting, maintaining, and repairing electrical wiring, fixtures, control systems, and power equipment',
                'base_responsibilities' => [
                    'Install, inspect, and maintain electrical wiring, conduits, fixtures, and control panels.',
                    'Diagnose and repair electrical faults, short circuits, and power distribution issues.',
                    'Perform preventive maintenance on switchgear, transformers, generators, and electrical machinery.',
                    'Ensure all electrical work complies with safety codes and technical standards.',
                    'Maintain electrical maintenance records and inventory of electrical spares.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'sales targets', 'gst', 'recruitment']
            ],

            // HVAC / MAINTENANCE
            'maintenance_technician' => [
                'name'     => 'Maintenance Technician',
                'domain'   => 'HVAC / Maintenance',
                'aliases'  => ['maintenance technician', 'ac technician', 'hvac technician', 'service technician', 'maintenance engineer', 'field service technician'],
                'keywords' => ['maintenance', 'hvac', 'ac technician', 'chiller', 'plumbing', 'facility maintenance'],
                'purpose'  => 'performing preventive maintenance, servicing, and repairs for facility systems and mechanical equipment',
                'base_responsibilities' => [
                    'Perform routine preventive maintenance and inspections on facility equipment and HVAC systems.',
                    'Troubleshoot and repair mechanical, hydraulic, pneumatic, and utility system faults.',
                    'Respond promptly to maintenance requests and carry out equipment repairs.',
                    'Maintain service records, equipment manuals, and spare parts inventory.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'sales targets', 'gst', 'recruitment']
            ],

            // HEALTHCARE
            'nurse' => [
                'name'     => 'Staff Nurse',
                'domain'   => 'Healthcare',
                'aliases'  => ['nurse', 'staff nurse', 'medical assistant', 'nursing officer', 'clinical coordinator'],
                'keywords' => ['nurse', 'nursing', 'patient care', 'clinical', 'medication', 'hospital'],
                'purpose'  => 'delivering high-quality patient care, administering medications, and supporting clinical treatment workflows',
                'base_responsibilities' => [
                    'Provide direct patient care, monitor vital signs, and assess patient health condition.',
                    'Administer prescribed medications and treatments in accordance with clinical guidelines.',
                    'Maintain accurate medical records, nursing charts, and patient care documentation.',
                    'Assist physicians and surgeons during clinical procedures and patient examinations.',
                    'Ensure strict adherence to healthcare safety, infection control, and hygiene protocols.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'gst', 'sales targets', 'cctv']
            ],

            // FRONTEND DEVELOPMENT
            'frontend_developer' => [
                'name'     => 'Frontend Developer',
                'domain'   => 'Frontend Development',
                'aliases'  => ['frontend developer', 'front end developer', 'frontend engineer', 'ui developer', 'web ui developer'],
                'keywords' => ['frontend', 'html', 'css', 'javascript', 'react', 'angular', 'vue', 'ui developer'],
                'purpose'  => 'designing, developing, and maintaining efficient, reusable, and responsive web user interfaces',
                'base_responsibilities' => [
                    'Design, develop, and maintain responsive, high-performance web applications.',
                    'Build reusable frontend components using HTML, CSS, and modern JavaScript frameworks.',
                    'Collaborate with UI/UX designers and backend engineers to integrate application APIs.',
                    'Troubleshoot, debug, and optimize application performance and cross-browser compatibility.',
                    'Maintain code quality, performance standards, testing, and deployment workflows.'
                ],
                'blocked_terms' => ['prospect', 'lead generation', 'sales targets', 'sales presentations', 'revenue metrics', 'gst', 'recruitment']
            ],

            // BACKEND DEVELOPMENT
            'backend_developer' => [
                'name'     => 'Backend Developer',
                'domain'   => 'Backend Development',
                'aliases'  => ['backend developer', 'back end developer', 'node developer', 'php developer', 'java developer', 'python developer', '.net developer', 'laravel developer'],
                'keywords' => ['backend', 'php', 'laravel', 'codeigniter', 'node', 'java', 'express', 'spring', 'api'],
                'purpose'  => 'building, optimizing, and maintaining scalable server-side business logic, APIs, and databases',
                'base_responsibilities' => [
                    'Build and maintain scalable, secure backend services and RESTful APIs.',
                    'Design database schemas, write optimized queries, and manage data integrations.',
                    'Implement application security, authentication, and performance protocols.',
                    'Troubleshoot, debug, and upgrade server-side applications and microservices.'
                ],
                'blocked_terms' => ['prospect', 'lead generation', 'sales targets', 'sales presentations', 'revenue metrics', 'gst', 'recruitment']
            ],

            // SOFTWARE DEVELOPMENT
            'software_developer' => [
                'name'     => 'Software Developer',
                'domain'   => 'Software Development',
                'aliases'  => ['software developer', 'software engineer', 'programmer', 'full stack developer', 'web developer', 'application developer'],
                'keywords' => ['software', 'full stack', 'developer', 'web developer', 'app developer'],
                'purpose'  => 'designing, coding, testing, and deploying robust software solutions and web applications',
                'base_responsibilities' => [
                    'Design, code, test, and debug software applications according to functional requirements.',
                    'Develop backend services, database integrations, and application features.',
                    'Maintain code quality, architecture standards, testing, and release workflows.',
                    'Collaborate with cross-functional teams to resolve technical issues and deliver software features.'
                ],
                'blocked_terms' => ['prospect', 'lead generation', 'sales targets', 'sales presentations', 'revenue metrics', 'gst', 'recruitment']
            ],

            // DATA ENGINEERING
            'data_engineer' => [
                'name'     => 'Data Engineer',
                'domain'   => 'Data Engineering',
                'aliases'  => ['data engineer', 'etl engineer', 'pipeline engineer'],
                'keywords' => ['data engineer', 'etl', 'data pipeline', 'spark', 'hadoop', 'airflow'],
                'purpose'  => 'designing, constructing, and maintaining reliable data pipelines and data processing solutions',
                'base_responsibilities' => [
                    'Design, construct, test, and maintain scalable data architecture and processing pipelines.',
                    'Build robust ETL processes to aggregate structured and unstructured data from diverse sources.',
                    'Ensure data cleanliness, integrity, performance, and accessibility across data stores.',
                    'Collaborate with Data Scientists and Analysts to support reporting and analytics needs.'
                ],
                'blocked_terms' => ['prospect', 'lead generation', 'sales targets', 'sales presentations', 'revenue metrics', 'gst', 'recruitment']
            ],

            // HR / RECRUITMENT
            'hr_recruiter' => [
                'name'     => 'HR Recruiter',
                'domain'   => 'Human Resources',
                'aliases'  => ['hr recruiter', 'recruiter', 'talent acquisition specialist', 'talent acquisition executive', 'hr executive', 'hr generalist'],
                'keywords' => ['recruiter', 'recruitment', 'talent acquisition', 'sourcing', 'screening', 'onboarding'],
                'purpose'  => 'managing recruitment activities, candidate sourcing, screening, employee relations, and HR operations',
                'base_responsibilities' => [
                    'Source potential candidates through job portals, social networks, and professional databases.',
                    'Screen resumes, conduct preliminary phone interviews, and assess candidate suitability.',
                    'Coordinate interview schedules between candidates and hiring managers.',
                    'Manage employee onboarding, orientation, records keeping, and statutory compliance.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'cctv', 'sales targets', 'revenue growth', 'gst']
            ],

            // FINANCE / ACCOUNTS
            'accountant' => [
                'name'     => 'Accountant',
                'domain'   => 'Finance & Accounts',
                'aliases'  => ['accountant', 'senior accountant', 'accounts executive', 'accounts officer', 'finance executive'],
                'keywords' => ['accountant', 'accounts', 'tally', 'gst', 'finance', 'taxation', 'ledger', 'auditing'],
                'purpose'  => 'maintaining financial records, managing accounting transactions, and ensuring statutory compliance',
                'base_responsibilities' => [
                    'Prepare and maintain ledger accounts, financial statements, and vouchers.',
                    'Process accounts payable, accounts receivable, and vendor invoices.',
                    'Perform bank reconciliations and verify transaction accuracy.',
                    'Assist in monthly, quarterly, and annual financial closing activities.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'cctv', 'sales targets', 'lead generation', 'recruitment']
            ],

            // MARKETING
            'marketing_executive' => [
                'name'     => 'Marketing Executive',
                'domain'   => 'Marketing',
                'aliases'  => ['marketing executive', 'digital marketing executive', 'digital marketing specialist', 'seo executive', 'content writer'],
                'keywords' => ['marketing', 'digital marketing', 'seo', 'sem', 'social media', 'content writer', 'branding'],
                'purpose'  => 'executing marketing campaigns, enhancing brand presence, and driving customer engagement',
                'base_responsibilities' => [
                    'Plan and execute multi-channel marketing campaigns across digital and print media.',
                    'Manage social media channels, content creation, and promotional activities.',
                    'Track, analyze, and report on campaign performance and engagement metrics.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'cctv', 'gst', 'nursing']
            ],

            // PROCUREMENT
            'procurement_executive' => [
                'name'     => 'Procurement Executive',
                'domain'   => 'Procurement',
                'aliases'  => ['procurement executive', 'purchase executive', 'procurement manager', 'purchase manager', 'vendor management executive'],
                'keywords' => ['procurement', 'purchase', 'sourcing', 'rfq', 'vendor management'],
                'purpose'  => 'sourcing materials, negotiating supplier contracts, and managing purchasing workflows',
                'base_responsibilities' => [
                    'Source, evaluate, and select vendors for equipment, materials, and services.',
                    'Prepare RFQs, negotiate commercial terms, and finalize purchase orders.',
                    'Monitor vendor performance, delivery timelines, and product quality compliance.',
                    'Maintain procurement records, invoice verifications, and supplier databases.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'cctv', 'sales targets', 'recruitment']
            ],

            // ADMINISTRATION
            'admin_executive' => [
                'name'     => 'Admin Executive',
                'domain'   => 'Administration',
                'aliases'  => ['admin executive', 'administrative executive', 'office administrator', 'office coordinator', 'receptionist', 'front office executive'],
                'keywords' => ['admin', 'administration', 'office administrator', 'receptionist', 'front office', 'facility'],
                'purpose'  => 'managing office operations, facility maintenance, visitor handling, and administrative support',
                'base_responsibilities' => [
                    'Manage day-to-day office administration, facility maintenance, and supplies inventory.',
                    'Coordinate office logistics, travel arrangements, visitor reception, and official documentation.',
                    'Handle incoming calls, correspondence, and office filing systems.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'cctv', 'sales targets', 'gst', 'recruitment']
            ],

            // CUSTOMER SUPPORT
            'customer_support_executive' => [
                'name'     => 'Customer Support Executive',
                'domain'   => 'Customer Support',
                'aliases'  => ['customer support executive', 'customer service representative', 'helpdesk agent', 'technical support executive'],
                'keywords' => ['customer support', 'customer service', 'helpdesk', 'bpo', 'call center'],
                'purpose'  => 'assisting customers, resolving queries, and delivering high customer satisfaction',
                'base_responsibilities' => [
                    'Respond promptly and professionally to customer queries via call, email, or chat.',
                    'Investigate, troubleshoot, and resolve customer issues efficiently.',
                    'Log support tickets and follow up through to final resolution.'
                ],
                'blocked_terms' => ['software development', 'php', 'laravel', 'cctv', 'sales targets', 'gst', 'recruitment']
            ]
        ];
    }

    /**
     * STEP 6 & 11: Reusable Skill to Action Mapping Engine (Occupation/Domain-Aware)
     */
    private function getSkillMappings() {
        return [
            // SALES SKILLS
            'pipeline & crm management' => [
                'Maintain and update sales pipelines and CRM records to track opportunities, follow-ups, customer interactions, and deal progress.'
            ],
            'negotiation & closing' => [
                'Conduct negotiations with prospects and clients, address commercial concerns, and work toward successful deal closure.'
            ],
            'objection handling' => [
                'Handle customer objections by understanding concerns and presenting appropriate solutions to move opportunities forward.'
            ],
            'consultative selling & discovery' => [
                'Conduct discovery conversations to understand customer requirements, business challenges, and purchasing needs, and recommend suitable solutions.'
            ],
            'lead generation' => [
                'Identify and pursue new business opportunities through prospecting, outreach, and lead-generation activities.'
            ],
            'crm' => [
                'Maintain accurate customer and opportunity information in the CRM and track follow-ups throughout the sales cycle.'
            ],
            'sales presentation' => [
                'Conduct product and solution presentations based on customer requirements.'
            ],
            'client relationship management' => [
                'Build and maintain strong relationships with existing and prospective clients.'
            ],
            'sales target' => [
                'Track sales performance and work toward achieving assigned revenue and sales targets.'
            ],
            'negotiation' => [
                'Negotiate commercial terms with customers and work toward successful deal closure.'
            ],

            // SOFTWARE & TECH SKILLS
            'html' => [
                'Build clean and semantic HTML structures for responsive web applications.'
            ],
            'css' => [
                'Develop responsive and maintainable user interfaces using CSS.'
            ],
            'javascript' => [
                'Implement interactive functionality and client-side application behavior using JavaScript.'
            ],
            'react' => [
                'Develop reusable React components and maintain responsive user interfaces.'
            ],
            'angular' => [
                'Develop and maintain Angular components and application features.'
            ],
            'php' => [
                'Build and maintain server-side web applications using PHP.'
            ],
            'laravel' => [
                'Develop Laravel-based applications, APIs, modules, and backend business logic.'
            ],
            'node.js' => [
                'Develop backend services and APIs using Node.js.'
            ],
            'node' => [
                'Develop backend services and APIs using Node.js.'
            ],
            'sql' => [
                'Write and optimize SQL queries and support database-driven application functionality.'
            ],
            'api development' => [
                'Design, develop, integrate, and maintain APIs for application and system communication.'
            ],
            'git' => [
                'Use version control practices to manage source code, collaborate with development teams, and maintain release history.'
            ],
            'testing' => [
                'Design and execute tests to identify defects and verify application functionality.'
            ],
            'mern' => [
                'Develop full-stack web applications using MERN technologies.'
            ],
            'mean' => [
                'Develop full-stack web applications using MEAN technologies.'
            ],

            // DATA SKILLS
            'python' => [
                'Develop Python-based scripts, applications, and data processing workflows.'
            ],
            'power bi' => [
                'Develop dashboards and reports to support data analysis and business decision-making.'
            ],
            'etl' => [
                'Design and maintain ETL workflows to extract, transform, and load data from multiple sources.'
            ],
            'data analysis' => [
                'Analyze datasets to identify trends, patterns, and actionable insights.'
            ],

            // EXCEL (DOMAIN-AWARE DYNAMIC FUNCTION)
            'excel' => function($domain) {
                if ($domain === 'Finance & Accounts') {
                    return 'Use Excel for financial reporting, reconciliation, ledger documentation, and data management.';
                } elseif ($domain === 'Human Resources') {
                    return 'Use Excel for tracking employee records, HR analytics, and operational reporting.';
                } elseif ($domain === 'Sales') {
                    return 'Use Excel for sales pipeline tracking, revenue forecasting, and reporting.';
                }
                return 'Use Excel for reporting, data analysis, and operational tracking.';
            },

            // HR SKILLS
            'recruitment' => [
                'Source, screen, and coordinate candidates throughout the recruitment process.'
            ],
            'candidate screening' => [
                'Review resumes and evaluate candidates against job requirements.'
            ],
            'interviewing' => [
                'Coordinate and conduct interviews and document candidate evaluations.'
            ],
            'onboarding' => [
                'Coordinate onboarding activities and ensure required documentation is completed.'
            ],
            'employee relations' => [
                'Support employee concerns and coordinate appropriate HR actions.'
            ],
            'payroll' => [
                'Support payroll processing and maintain accurate employee payroll records.'
            ],

            // FINANCE SKILLS
            'tally' => [
                'Maintain accounting transactions and financial records using Tally.'
            ],
            'gst' => [
                'Support GST-related documentation, filings, and compliance activities.'
            ],
            'accounts payable' => [
                'Process vendor invoices, verify supporting documents, and maintain accounts payable records.'
            ],
            'accounts receivable' => [
                'Track customer payments, maintain receivable records, and follow up on outstanding balances.'
            ],
            'bank reconciliation' => [
                'Perform bank reconciliations and investigate discrepancies between bank and accounting records.'
            ],
            'financial reporting' => [
                'Prepare financial reports and supporting schedules for management review.'
            ],

            // CCTV / SECURITY SKILLS
            'cctv' => [
                'Install, configure, test, and maintain CCTV surveillance systems.'
            ],
            'dvr' => [
                'Configure DVR systems and verify video recording and storage functionality.'
            ],
            'nvr' => [
                'Configure and maintain NVR systems and ensure reliable network video recording.'
            ],
            'ip camera' => [
                'Install and configure IP cameras and verify network connectivity and video quality.'
            ],
            'surveillance' => [
                'Monitor and maintain surveillance systems to ensure reliable security coverage.'
            ],
            'troubleshooting' => [
                'Diagnose and resolve equipment, connectivity, and system faults.'
            ],

            // ELECTRICAL SKILLS
            'electrical wiring' => [
                'Install, inspect, and maintain electrical wiring and connections according to safety standards.'
            ],
            'electrical maintenance' => [
                'Perform preventive and corrective maintenance on electrical equipment and systems.'
            ],
            'electrical safety' => [
                'Follow electrical safety procedures and ensure compliance with applicable technical standards.'
            ],

            // HEALTHCARE SKILLS
            'patient care' => [
                'Provide appropriate patient care and monitor patient conditions according to established procedures.'
            ],
            'clinical documentation' => [
                'Maintain accurate patient and clinical records.'
            ],
            'medication' => [
                'Administer medications according to prescribed instructions and established clinical procedures.'
            ]
        ];
    }
}
