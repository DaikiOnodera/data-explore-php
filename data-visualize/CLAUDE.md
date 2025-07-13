# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PHP data visualization project that uses the QuickChart.io API to generate chart images from Chart.js configurations without requiring server-side image generation.

## Commands

### Development Commands
- **Install dependencies**: `composer install`
- **Run visualization script**: `composer run visualize`
  - Outputs a QuickChart.io URL that generates a 500x300px PNG line chart

### Common Tasks
- **Update dependencies**: `composer update`
- **Add new dependency**: `composer require <package-name>`

## Architecture

### Core Components
- **src/visualize.php**: Main entry point that:
  - Generates monthly labels starting from current month
  - Creates Chart.js configuration for line charts
  - Uses QuickChart PHP client to generate chart URLs
  - Currently uses hardcoded data: [65, 59, 80, 81, 56, 55, 40]

### Dependencies
- **ianw/quickchart**: PHP client for QuickChart.io API
  - No API key required for basic usage
  - Generates chart images from Chart.js configurations
  - Returns publicly accessible URLs to PNG images

### Project Structure
```
/data-visualize/
├── composer.json      # Defines scripts and dependencies
├── src/
│   └── visualize.php  # Chart generation logic
└── vendor/            # Composer dependencies
```

## Key Implementation Details

1. **Namespace**: `Daikionodera\DataVisualize` (PSR-4 autoloaded from `src/`)

2. **Chart Configuration**: Uses standard Chart.js configuration format:
   - Type: 'line'
   - Dataset includes: label, data array, borderColor, fill setting
   - Options include: responsive behavior and title configuration

3. **QuickChart Integration**:
   - Chart configuration is passed to `QuickChart` constructor
   - `getUrl()` method returns the generated chart image URL
   - No local image files are created; everything is handled via API

## Development Notes

- The project currently has no test suite
- Data values are hardcoded in visualize.php
- Month labels are dynamically generated based on current date
- Output is a simple URL string (no HTML rendering or file saving)