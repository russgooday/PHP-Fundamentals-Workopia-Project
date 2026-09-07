<?php
namespace Spikes\Fixtures;

class UnresolvableClass {
    public function __construct(string $host, int $port) {}
}

class Database {
    public function __construct(
        private string $host,
        private string $user,
        private string $password,
        private string $database
    ) {}
}

class Model {
    public function __construct(
        private Database $database
    ) {}

    public function findAll($limit): array {
        return [];
    }
}

class Logger {
    public function log(string $message): void {
        echo "[LOG] {$message}\n";
    }
}

class Viewer {
    public function render(string $view, array $data = []): string {
        return "<rendered: {$view}>";
    }
}

class Listings {
    public function __construct(
        private Logger $logger,
        private Model $model
    ) {}

    public function findAll(int $limit): array {
        $this->logger->log("Fetching {$limit} listings");
        $this->model->findAll($limit);
        return [];
    }
}

class Mailer {
    public function __construct(
        private Logger $logger
    ) {}

    public function send(string $to): void {
        $this->logger->log("Sending mail to {$to}");
    }
}

// Depends on TWO things, one of which (Logger) is also a dependency
// further down the tree elsewhere — good check that your resolver
// doesn't do anything strange when the same class is needed twice.
class Notifier {
    public function __construct(
        private Mailer $mailer,
        private Logger $logger
    ) {}

    public function notify(string $message): void {
        $this->logger->log("Notifying: {$message}");
        $this->mailer->send('someone@example.com');
    }
}

// Three levels deep overall: ListingsController -> Notifier -> Mailer -> Logger
class ListingsController {
    public function __construct(
        private Viewer $viewer,
        private Listings $listings,
        private Notifier $notifier
    ) {}

    public function index(): void {
        $this->notifier->notify('Listings index was viewed');
        echo $this->viewer->render('listings/index', [
            'listings' => $this->listings->findAll(4)
        ]);
        echo "\n";
    }
}
