<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScraperService
{
    public function getProjectInfo(string $repo)
    {
        $headers = ['Authorization' => 'Bearer ' . env('GITHUB_TOKEN')];

        $output = ['project' => [], 'images' => []];
        $project = [];
        try {
            $api_response = Http::withHeaders($headers)->get("https://api.github.com/repos/{$repo}");
            $api_response->throw();
            if ($api_response->getStatusCode() == 200) {
                $content = $api_response->getBody()->getContents();
                $decoded = json_decode($content, true);
                //dd($decoded);
                $project['title'] = $decoded['name'];
                $project['full_name'] = $decoded['full_name'];
                $project['keywords'] = implode(", ", $decoded['topics']);

                $project['short_description'] = $decoded['description'];
                $project['stars'] = $decoded['stargazers_count'];
                $project['git_link'] = $decoded['html_url'];
                $project['demo_link'] = $decoded['homepage'];
                $project['default_branch'] = $decoded['default_branch'];


                $readme = "https://raw.githubusercontent.com/" . $decoded["full_name"] . "/" . $decoded["default_branch"] . "/README.md";
                $api_response = Http::withHeaders($headers)->get($readme);
                if ($api_response->getStatusCode() == 404) {
                    $readme = "https://raw.githubusercontent.com/" . $decoded["full_name"] . "/" . $decoded["default_branch"] . "/readme.md";
                    $api_response = Http::withHeaders($headers)->get($readme);
                }
                if ($api_response->getStatusCode() == 200) {
                    $content = $api_response->getBody()->getContents();

                    $converter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
                        'html_input' => 'allow',
                        'allow_unsafe_links' => false,
                    ]);

                    $html = $converter->convert($content);
                    $project['description'] = (string) $html;

                    $images = $this->extractImageSources($html);
                    $filtered = [];
                    foreach ($images as $image) {
                        if (
                            strpos($image, "badge") !== false ||
                            strpos($image, "img.shields.io") !== false ||
                            strpos($image, "contributors") !== false ||
                            strpos($image, "logo.") !== false ||
                            strpos($image, "shield.") !== false ||
                            strpos($image, "pugx.org") !== false ||
                            strpos($image, "travis-ci") !== false ||
                            strpos($image, "app.codeship.com") !== false ||
                            strpos($image, "poser.pugx") !== false ||

                            strpos($image, "paypalobjects") !== false ||
                            strpos($image, "paypalLogo") !== false ||
                            strpos($image, "lang_flags") !== false ||
                            strpos($image, "button.") !== false ||
                            strpos($image, "discord_banner") !== false ||
                            strpos($image, "via.placeholder.com") !== false
                        ) {
                            continue;
                        } else if (substr($image, 0, 4) == 'http') {
                            $filtered[] = $image;
                        } else {
                            $filtered[] = "https://raw.githubusercontent.com/" . $decoded["full_name"] . "/" . $decoded["default_branch"] . "/" . $image;
                        }
                    }

                    $output['images'] = $filtered;
                    $output['project'] = $project;
                    return $output;
                }
                else{
                    throw new \Exception("Error fetching readme from GitHub API");
                }
            }
            else{
                throw new \Exception("Error fetching project data from GitHub API");
            }
        } catch (\Exception $e) {
            Log::info("getProjectInfo Failed with arguments: $repo");
            Log::info($e->getMessage());
            throw $e;
        }

        return false;
    }

    private function extractImageSources($htmlString)
    {
        $imageSources = array();

        // Create a new DOMDocument object
        $dom = new \DOMDocument();

        // Disable error reporting for invalid HTML
        libxml_use_internal_errors(true);

        // Load the HTML string into the DOMDocument
        $dom->loadHTML($htmlString);

        // Enable error reporting again
        libxml_use_internal_errors(false);

        // Find all <img> tags in the HTML
        $imageTags = $dom->getElementsByTagName('img');

        // Iterate through each <img> tag
        foreach ($imageTags as $imageTag) {
            // Get the value of the src attribute
            $src = $imageTag->getAttribute('src');

            // Add the src value to the array
            $imageSources[] = $src;
        }

        return $imageSources;
    }
}