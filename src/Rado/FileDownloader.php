<?php

// zwróć uwagę na to, że deklaracja trybu strict znajduje się w każdym pliku
declare(strict_types=1);

// przestrzeń Rado, pozwala nam mieć pewność, że nie będzie kolizji nazw klas
// z innymi bibliotekami, a także pozwoli nam na otwarcie kodu do użcyia
// w innych projektach
namespace Rado;

// aliasy, czyli informujemy PHP, że w tej klasie będziemy mogli używać
// skróconych nazw: Config, Logger i Helper, które będą się odnosić do odpowiednich
// nazw pełnych:
use Rado\App\{Config, Logger};
use Rado\Tool\Helper;

/**
 * Klasa odpowiedzialna za pobieranie plikow ze zdalnych serwerow.
 */
class FileDownloader
{
    /**
     * Konfiguracja aplikacji.
     *
     * @var Rado\App\Config
     */
    private $config;

    /**
     * Obiekt z metodami pomocniczymi.
     *
     * @var Rado\Tool\Helper
     */
    private $helper;

    /**
     * Instancja loggera.
     *
     * @var Rado\App\Logger
     */
	private $logger;

    /**
     * Pobranie pliku z podanego adresu URL oraz zwrócenie jego treści (bez zapisywania na filesystemie).
     *
     * @param string $url adres URL, spod którego pobieramy plik
     *
     * @return string
     */
    public function download(string $url): string
    {
        $this->logger->saveLog("Rozpoczecie pobierania pliku: {$url}");

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);

        return $response->getBody()->getContents();
    }

    /**
     * Metoda pobiera pliki z lokalizacji określonej na podstawie podanego typu i zapisuje je do pliku.
     *
     * @todo wydaje się, że ta metoda mogłaby być rozbita na mniejsze, przez co stałaby się prostsza
     *
     * @param string[] $titles tablica tytułów filmów
     * @param string $type typ plików do pobrania
     */
    public function downloadFiles(array $titles, string $type): void
    {
        $html = $this->download($this->config->getUrl($type));
        $remoteFileNames = $this->helper->extractFileNamesFromHtml($html);

        foreach ($remoteFileNames as $remoteFileName) {
            $name = $this->helper->convertTitleToUrl($titles[$remoteFileName-1]);
            $this->downloadToFile($this->config->getUrl($type). $remoteFileName . ".jpg", $type . '/'.$name.".jpg");
        }
    }

    /**
     * Pobranie pliku spod adresu URL i zapisanie do wskazanego pliku na filesystemie.
     *
     * @param string $url adres URL, spod którego pobieramy plik
     * @param string $destinationFile docelowe miejsce zapisu pliku
     */
    public function downloadToFile(string $url, string $destinationFile): void
    {
        file_put_contents($destinationFile, $this->download($url));
        $this->logger->saveLog("Zakonczenie pobierania pliku: {$destinationFile}");
    }

    /**
     * Metoda ustawia konfigurację.
     *
     * @param Rado\App\Config $config obiekt konfiguracji
     *
     * @return Rado\FileDownloader
     */
    public function setConfig(Config $config): FileDownloader
    {
            $this->config = $config;

            return $this;
    }

    /**
     * Metoda ustawia obiekt z pomocniczymi metodami do projektu.
     *
     * @param Rado\Tool\Helper $helper obiekt z metodami pomocniczymi
     *
     * @return Rado\FileDownloader
     */
    public function setHelper(Helper $helper): FileDownloader
    {
            $this->helper = $helper;

            return $this;
    }

    /**
     * Metoda ustawia loggera.
     *
     * @param Rado\App\Logger $logger obiekt loggera
     *
     * @return Rado\FileDownloader
     */
	public function setLogger(Logger $logger): FileDownloader
	{
		$this->logger = $logger;

		return $this;
	}
}
