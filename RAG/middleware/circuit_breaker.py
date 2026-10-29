import time
import logging

logger = logging.getLogger("circuit_breaker")

class CircuitBreaker:
    def __init__(self, failure_threshold: int = 5, recovery_timeout: int = 30):
        self.failure_threshold = failure_threshold
        self.recovery_timeout = recovery_timeout
        self.failure_count = 0
        self.last_failure_time = 0
        self.state = "CLOSED"  # CLOSED (healthy), OPEN (failing fast), HALF_OPEN (recovering)

    def record_failure(self):
        self.failure_count += 1
        self.last_failure_time = time.time()
        if self.failure_count >= self.failure_threshold:
            if self.state != "OPEN":
                logger.warning("Circuit breaker TRIPPED! Fast failing all requests.")
            self.state = "OPEN"

    def record_success(self):
        if self.state == "HALF_OPEN":
            logger.info("Circuit breaker RECOVERED! Service restored.")
        self.failure_count = 0
        self.state = "CLOSED"

    def can_execute(self) -> bool:
        if self.state == "CLOSED":
            return True
        elif self.state == "OPEN":
            if time.time() - self.last_failure_time >= self.recovery_timeout:
                # Time to test if the service is back up
                self.state = "HALF_OPEN"
                return True
            return False
        # HALF_OPEN allows exactly one request through to test healthy status
        return False

# Global circuit breakers for specific external services
pinecone_cb = CircuitBreaker(failure_threshold=3, recovery_timeout=45)
mysql_cb = CircuitBreaker(failure_threshold=3, recovery_timeout=30)
