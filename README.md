# Email Extractor
A simple PHP tool to extract email addresses based on a website, email domain, and niche, and export them into an Excel file.

## Features
- Extracts emails from Google search results.
- Exports emails to an Excel file (.xlsx).
- User-friendly web interface.

## Requirements
- PHP 7.4 or higher
- Composer
- A web server (e.g., Apache or Nginx)

## Installation
1. Clone the repository and navigate to the directory:
   ```bash
   git clone https://github.com/anupgautamm/email-extractor.git
   cd <project-directory>
   ```
2. Install dependencies:
   ```bash
   composer install
   ```

## Usage
1. Deploy the project to a web server.
    ```terminal
    php -S localhost:8000
    ```
3. Open the app in your browser.
4. Enter the target website, email domain, and niche.
5. Click **Extract Emails** to download the Excel file.
