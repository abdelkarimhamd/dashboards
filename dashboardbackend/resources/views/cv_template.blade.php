<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $employee->first_name }} {{ $employee->last_name }}'s CV</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #fff;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 15px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            overflow: hidden;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            max-width: 150px;
            height: auto;
        }

        h1 {
            color: #264380;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 15px;
        }

        h2.section-title {
            color: #75913b;
            font-size: 1.5rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #264380;
            padding-bottom: 5px;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .content-item {
            margin-bottom: 15px;
            overflow-wrap: break-word;
        }

        .content-item h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #264380;
            font-weight: bold;
        }

        p {
            font-size: 0.95rem;
            margin: 5px 0;
        }

        p strong {
            color: #75913b;
        }

        .about-me, .languages {
            background-color: #eef2f7;
            padding: 10px;
            border-radius: 5px;
            font-size: 1rem;
            white-space: pre-wrap;
            margin-bottom: 15px;
        }

        .experience-title {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            flex-wrap: wrap;
        }

        .experience-title h3 {
            font-size: 1.1rem;
            color: #264380;
            margin-right: 10px;
        }

        .experience-dates {
            color: #555;
            font-style: italic;
            font-size: 0.9rem;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-item {
            background-color: #eef2f7;
            border-radius: 5px;
            padding: 8px;
            font-size: 0.9rem;
            flex: 1 1 calc(33% - 20px);
            box-sizing: border-box;
        }

        .skill-item h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            color: #264380;
        }

        /* Ensure that content doesn't break in the middle of sections */
        .page-break {
            page-break-after: always;
        }

        /* Adjustments for PDF export */
        @media print {
            body {
                margin: 0;
            }

            .container {
                padding: 0;
                width: 100%;
            }

            .section {
                page-break-inside: avoid;
            }

            .experience-title {
                flex-wrap: nowrap;
            }

            .skills-list {
                flex-wrap: wrap;
            }

            .skill-item {
                flex: 1 1 30%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo-container">
            <img         src="data:image/png;base64,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"
 alt="Company Logo">
        </div>

        <h1>{{ $employee->first_name }} {{ $employee->last_name }}'s CV</h1>

        <!-- About Me -->
        @if(!empty($employee->about_me) && strtolower($employee->about_me) !== 'n/a')
        <div class="section">
            <h2 class="section-title">About Me</h2>
            <div class="about-me">
                <p>{{ $employee->about_me }}</p>
            </div>
        </div>
        @endif

        <!-- Proposed Position and Nationality -->
        @if(!empty($employee->proposed_position) || !empty($employee->nationality) || !empty($employee->years_experience))
        <div class="section">
            <h2 class="section-title">Proposed Position & Nationality</h2>
            <div class="content-item">
                @if(!empty($employee->proposed_position) && strtolower($employee->proposed_position) !== 'n/a')
                <p><strong>Proposed Position:</strong> {{ $employee->proposed_position }}</p>
                @endif
                @if(!empty($employee->nationality) && strtolower($employee->nationality) !== 'n/a')
                <p><strong>Nationality:</strong> {{ $employee->nationality }}</p>
                @endif
                @if(!empty($employee->years_experience) && strtolower($employee->years_experience) !== 'n/a')
                <p><strong>Years of Experience:</strong> {{ $employee->years_experience }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Experiences -->
        @if($employee->experiences->isNotEmpty())
        <div class="section">
            <h2 class="section-title">Experiences</h2>
            @foreach($employee->experiences as $experience)
                @if(!empty($experience->company_name) || !empty($experience->position) || !empty($experience->description))
                <div class="content-item">
                    <div class="experience-title">
                        @if(!empty($experience->company_name) && strtolower($experience->company_name) !== 'n/a')
                        <h3>{{ $experience->company_name }}</h3>
                        @endif
                        @if(!empty($experience->start_date) && strtolower($experience->start_date) !== 'n/a')
                        <p class="experience-dates">
                            {{ $experience->start_date }} - {{ $experience->end_date ?? 'Present' }}
                        </p>
                        @endif
                    </div>
                    @if(!empty($experience->position) && strtolower($experience->position) !== 'n/a')
                    <p><strong>Position:</strong> {{ $experience->position }}</p>
                    @endif
                    @if(!empty($experience->description) && strtolower($experience->description) !== 'n/a')
                    <p><strong>Responsibilities & Achievements:</strong></p>
                    <div class="about-me">
                        <p>{{ $experience->description }}</p>
                    </div>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Education -->
        @if($employee->education->isNotEmpty())
        <div class="section">
            <h2 class="section-title">Education</h2>
            @foreach($employee->education as $education)
                @if(!empty($education->institution_name) || !empty($education->degree) || !empty($education->field_of_study))
                <div class="content-item">
                    @if(!empty($education->institution_name) && strtolower($education->institution_name) !== 'n/a')
                    <h3>{{ $education->institution_name }}</h3>
                    @endif
                    @if(!empty($education->degree) && strtolower($education->degree) !== 'n/a')
                    <p><strong>Degree:</strong> {{ $education->degree }}</p>
                    @endif
                    @if(!empty($education->field_of_study) && strtolower($education->field_of_study) !== 'n/a')
                    <p><strong>Field of Study:</strong> {{ $education->field_of_study }}</p>
                    @endif
                    @if(!empty($education->start_date) && strtolower($education->start_date) !== 'n/a')
                    <p>
                        {{ $education->start_date }} - {{ $education->end_date ?? 'Present' }}
                    </p>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Skills -->
        @if($employee->skills->isNotEmpty())
        <div class="section">
            <h2 class="section-title">Skills</h2>
            <div class="skills-list">
                @foreach($employee->skills as $skill)
                    @if(!empty($skill->skill_name) && strtolower($skill->skill_name) !== 'n/a')
                    <div class="skill-item">
                        <h4>{{ $skill->skill_name }}</h4>
                        @if(!empty($skill->proficiency_level) && strtolower($skill->proficiency_level) !== 'n/a')
                        <p>Proficiency Level: {{ $skill->proficiency_level }}</p>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Certifications -->
        @if($employee->certifications->isNotEmpty())
        <div class="section">
            <h2 class="section-title">Certifications</h2>
            @foreach($employee->certifications as $certification)
                @if(!empty($certification->certification_name) || !empty($certification->institution) || !empty($certification->issue_date))
                <div class="content-item">
                    @if(!empty($certification->certification_name) && strtolower($certification->certification_name) !== 'n/a')
                    <h3>{{ $certification->certification_name }}</h3>
                    @endif
                    @if(!empty($certification->institution) && strtolower($certification->institution) !== 'n/a')
                    <p><strong>Institution:</strong> {{ $certification->institution }}</p>
                    @endif
                    @if(!empty($certification->issue_date) && strtolower($certification->issue_date) !== 'n/a')
                    <p><strong>Issued Date:</strong> {{ $certification->issue_date }}</p>
                    @endif
                    @if(!empty($certification->expiry_date) && strtolower($certification->expiry_date) !== 'n/a')
                    <p><strong>Expiry Date:</strong> {{ $certification->expiry_date }}</p>
                    @endif
                </div>
                @endif
            @endforeach
        </div>
        @endif

        <!-- Languages -->
        @if(!empty($employee->languages) && strtolower($employee->languages) !== 'n/a')
        <div class="section">
            <h2 class="section-title">Languages</h2>
            <div class="languages">
                <p>{{ $employee->languages }}</p>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
