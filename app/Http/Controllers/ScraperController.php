<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;

class ScraperController extends Controller
{
    public function scrape($isbn)
    {

        // Configure Chrome capabilities
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability('chromeOptions', ['args' => ['--headless']]);

        // Connect to the existing Chrome WebDriver instance running on port 9515
        $driver = RemoteWebDriver::create('http://localhost:9515', $capabilities);

        try {
            // Navigate to the Goodreads shelf page
            $driver->get('https://www.goodreads.com/shelf/show/translated');

            // Find the search bar and enter the ISBN
            $searchBar = $driver->findElement(WebDriverBy::className('searchBox__input--navbar'));
            $searchBar->sendKeys($isbn);

            // Find and click the search icon
            $searchIcon = $driver->findElement(WebDriverBy::className('searchBox__icon--navbar'));
            $searchIcon->click();

            // Wait for the popup
            $wait = new WebDriverWait($driver, 10);
            $wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('LoginInterstitial')));

            // Hide popup
            $popupicon = $driver->findElement(WebDriverBy::xpath('//button[@aria-label="Close"]'));
            $popupicon->click();

            // Wait for the book details page to load
            $wait = new WebDriverWait($driver, 10);
            $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('SocialSignalCard')));
        
            // Click the "Book details & editions" button
            $detailsButton = $driver->findElement(WebDriverBy::xpath('//button[@aria-label="Book details and editions"]'));
            $detailsButton->click();

            // Wait for the book details to load
            $wait = new WebDriverWait($driver, 5);
            $wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('WorkDetails')));

            // Extract specific details
            $bookDetails = $driver->findElement(WebDriverBy::className('BookDetails'))->getText();

            // Quit Chrome WebDriver
            $driver->quit();

            // Return the scraped book details
            return response()->json(['book_details' => $bookDetails]);

        } catch (\Exception $e) {
            // Handle any exceptions
            $driver->quit();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
