<?php
/**
 * SpamCustomerCleaner - PrestaShop Module
 *
 * @author    Genisoft / LeBiggyFood
 * @copyright 2026
 * @license   Commercial / Proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class SpamDetector
{
    const FREE_DELETE_LIMIT = 50;

    /**
     * Check if module has active PRO license
     *
     * @return bool
     */
    public static function isPro()
    {
        $key = trim(Configuration::get('SCC_LICENSE_KEY'));
        if (empty($key)) {
            return false;
        }

        // Accept GENI-*, PRO-*, or 12+ char valid key (Genisoft licensing)
        $clean = strtoupper(str_replace(['-', ' '], '', $key));
        if (strpos($clean, 'GENI') === 0 || strpos($clean, 'PRO') === 0 || strpos($clean, 'SHOP') === 0) {
            return true;
        }

        return strlen($clean) >= 12;
    }

    /**
     * Get remaining free deletions allowed
     *
     * @return int
     */
    public static function getRemainingFreeDeletions()
    {
        if (self::isPro()) {
            return 999999;
        }

        $deletedSoFar = (int) Configuration::get('SCC_FREE_DELETED_COUNT', 0);
        return max(0, self::FREE_DELETE_LIMIT - $deletedSoFar);
    }

    /**
     * Get default detection criteria configuration
     */
    public static function getDefaultCriteria()
    {
        $getConfig = function ($key, $default = 1) {
            $val = Configuration::get($key);
            if ($val === false || $val === null || $val === '') {
                return $default;
            }
            return (bool) $val;
        };

        $whitelist = Configuration::get('SCC_WHITELIST');
        if ($whitelist === false || $whitelist === null) {
            $whitelist = "lefi0\ngenisoft\nadmin\nbiggyfood";
        }

        return [
            'check_urls' => $getConfig('SCC_CHECK_URLS', 1),
            'check_keywords' => $getConfig('SCC_CHECK_KEYWORDS', 1),
            'check_mixed_case' => $getConfig('SCC_CHECK_MIXED_CASE', 1),
            'check_consonants' => $getConfig('SCC_CHECK_CONSONANTS', 1),
            'check_vowels' => $getConfig('SCC_CHECK_VOWELS', 1),
            'check_consonant_start' => $getConfig('SCC_CHECK_CONSONANT_START', 1),
            'check_same_name' => $getConfig('SCC_CHECK_SAME_NAME', 1),
            'check_disposable' => $getConfig('SCC_CHECK_DISPOSABLE', 1),
            'check_tlds' => $getConfig('SCC_CHECK_TLDS', 1),
            'check_random_email' => $getConfig('SCC_CHECK_RANDOM_EMAIL', 1),
            'protect_orders' => true, // ALWAYS TRUE for safety
            'whitelist' => $whitelist,
        ];
    }

    /**
     * Get global database customer statistics
     */
    public static function getGlobalStats()
    {
        $db = Db::getInstance();
        $totalCustomers = (int) $db->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'customer`');
        $totalOrders = (int) $db->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'orders`');
        $totalAddresses = (int) $db->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'address`');
        $customersWithAddr = (int) $db->getValue('SELECT COUNT(DISTINCT id_customer) FROM `' . _DB_PREFIX_ . 'address` WHERE id_customer > 0');
        $customersWithOrders = (int) $db->getValue('SELECT COUNT(DISTINCT id_customer) FROM `' . _DB_PREFIX_ . 'orders` WHERE id_customer > 0');

        // Contact spam stats
        $totalContactThreads = (int) $db->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'customer_thread`');
        $contactSpamCount = (int) $db->getValue('
            SELECT COUNT(DISTINCT ct.id_customer_thread) 
            FROM `' . _DB_PREFIX_ . 'customer_thread` ct
            JOIN `' . _DB_PREFIX_ . 'customer_message` cm ON ct.id_customer_thread = cm.id_customer_thread
            WHERE cm.message REGEXP "http|www\\.|talkwithwebvisitor|seo-package|dating|viagra|casino"
               OR ct.email LIKE "%.ru" OR ct.email LIKE "%.xyz"
        ');

        return [
            'total_customers' => $totalCustomers,
            'total_orders' => $totalOrders,
            'total_addresses' => $totalAddresses,
            'customers_with_addr' => $customersWithAddr,
            'customers_with_orders' => $customersWithOrders,
            'total_contact_threads' => $totalContactThreads,
            'contact_spam_count' => $contactSpamCount,
            'is_pro' => self::isPro(),
            'free_limit' => self::FREE_DELETE_LIMIT,
            'remaining_free' => self::getRemainingFreeDeletions(),
            'last_cron_date' => Configuration::get('SCC_LAST_CRON_DATE'),
            'last_cron_stats' => Configuration::get('SCC_LAST_CRON_STATS'),
        ];
    }

    /**
     * Evaluate if a customer is spam according to heuristics
     *
     * @param array $c Customer record array
     * @param array &$reasons Output list of detection reasons
     * @param array $criteria Active criteria
     * @return bool
     */
    public static function isSpam($c, &$reasons = [], $criteria = null)
    {
        if ($criteria === null) {
            $criteria = self::getDefaultCriteria();
        }

        // Safety 1: Absolute protection for customers with orders
        if (!empty($c['nb_orders']) && (int) $c['nb_orders'] > 0) {
            return false;
        }

        $email = trim(isset($c['email']) ? $c['email'] : '');
        $fn = trim(isset($c['firstname']) ? $c['firstname'] : '');
        $ln = trim(isset($c['lastname']) ? $c['lastname'] : '');
        $fullName = $fn . ' ' . $ln;

        // Safety 2: Whitelist protection
        if (!empty($criteria['whitelist'])) {
            $whitelistTerms = preg_split('/[\r\n,]+/', $criteria['whitelist']);
            foreach ($whitelistTerms as $term) {
                $term = trim($term);
                if (!empty($term) && (stripos($email, $term) !== false || stripos($fullName, $term) !== false)) {
                    return false;
                }
            }
        }

        // Rule 1: URLs or domain names in firstname or lastname
        if (!empty($criteria['check_urls'])) {
            if (preg_match('/(http|https|www\.|\.com|\.ru|\.es|\.us|\.ly|\.top|\.xyz|\.link|\.club|\.online|\.site|\.vip|\.info|\.me\/)/i', $fullName)) {
                $reasons[] = 'Lien publicitaire ou URL dans le nom';
            }
        }

        // Rule 2: Spam keywords in name (dating, sexy, casino...)
        if (!empty($criteria['check_keywords'])) {
            if (preg_match('/(dating|waiting for you|wants to meet|wants to date|meet women|meet girls|sexy|casino|viagra|porn|escort|hookup)/i', $fullName)) {
                $reasons[] = 'Mots-clés de spam (dating/rencontre/adulte)';
            }
        }

        // Rule 3: CamelCase / Mixed Case bot patterns (e.g. GJfwUoNDU, oDglEQIqBVVMI, kAgLLaqWZpqInsg)
        if (!empty($criteria['check_mixed_case'])) {
            $isMixed = false;
            if (preg_match('/[a-z]{1,}[A-Z]{1,}[a-z]{1,}[A-Z]/', $fn) || preg_match('/[a-z]{1,}[A-Z]{1,}[a-z]{1,}[A-Z]/', $ln) ||
                preg_match('/[A-Z]{2,}[a-z]{2,}[A-Z]{2,}/', $fn) || preg_match('/[A-Z]{2,}[a-z]{2,}[A-Z]{2,}/', $ln)) {
                $isMixed = true;
            } elseif ((preg_match('/[a-z][A-Z]/', $fn) && !preg_match('/^(Mac|Mc|De|Le|Du|Von|Van|Fitz|St|Saint|D\')/i', $fn)) ||
                      (preg_match('/[a-z][A-Z]/', $ln) && !preg_match('/^(Mac|Mc|De|Le|Du|Von|Van|Fitz|St|Saint|D\')/i', $ln))) {
                if (!preg_match('/[- \']/', $fn) && !preg_match('/[- \']/', $ln)) {
                    $isMixed = true;
                }
            }
            if ($isMixed) {
                $reasons[] = 'Alternance majuscules/minuscules aléatoire (Bot)';
            }
        }

        // Rule 4: Consecutive consonants (5+ consonants in name)
        if (!empty($criteria['check_consonants'])) {
            $consonantsRegex = '/[bcdfghjklmnpqrstvwxzBCDFGHJKLMNPQRSTVWXZ]{5,}/u';
            if (preg_match($consonantsRegex, $fn) || preg_match($consonantsRegex, $ln)) {
                $reasons[] = '5+ consonnes consécutives (Inintelligible)';
            }
        }

        // Rule 5: Low vowel ratio (< 17% vowels on length >= 5)
        if (!empty($criteria['check_vowels'])) {
            $cleanFn = preg_replace('/[^a-zA-ZÀ-ÿ]/u', '', $fn);
            $cleanLn = preg_replace('/[^a-zA-ZÀ-ÿ]/u', '', $ln);
            $fnLen = mb_strlen($cleanFn, 'UTF-8');
            $lnLen = mb_strlen($cleanLn, 'UTF-8');
            $vowelRegex = '/[aeiouyàâäéèêëîïôöùûüÿAEIOUYÀÂÄÉÈÊËÎÏÔÖÙÛÜŸ]/u';

            if ($fnLen >= 5) {
                $vowels = preg_match_all($vowelRegex, $cleanFn);
                if ($vowels === 0 || ($vowels / $fnLen) < 0.17) {
                    $reasons[] = 'Prénom anormal sans voyelles suffisantes';
                }
            }
            if ($lnLen >= 5) {
                $vowels = preg_match_all($vowelRegex, $cleanLn);
                if ($vowels === 0 || ($vowels / $lnLen) < 0.17) {
                    $reasons[] = 'Nom anormal sans voyelles suffisantes';
                }
            }
        }

        // Rule 6: Abnormal consonant prefix at start
        if (!empty($criteria['check_consonant_start'])) {
            $suspiciousStartRegex = '/^[bcdfghjklmnpqrstvwxz]{3,}/iu';
            if ((preg_match($suspiciousStartRegex, $fn) && !preg_match('/^(chr|str|thr|scr|spl|spr|phr)/i', $fn)) ||
                (preg_match($suspiciousStartRegex, $ln) && !preg_match('/^(chr|str|thr|scr|spl|spr|phr)/i', $ln))) {
                $reasons[] = 'Début de nom avec consonnes atypiques';
            }
        }

        // Rule 7: Same firstname and lastname
        if (!empty($criteria['check_same_name'])) {
            if (mb_strtolower($fn, 'UTF-8') === mb_strtolower($ln, 'UTF-8') && mb_strlen($fn, 'UTF-8') >= 4) {
                if (preg_match('/(test|demo|admin|fake|asdf|qwerty)/i', $fn) || preg_match('/[a-z][A-Z]/', $fn)) {
                    $reasons[] = 'Nom et prénom identiques suspects';
                }
            }
        }

        // Rule 8: Disposable email services
        if (!empty($criteria['check_disposable'])) {
            if (preg_match('/@(test\.fr|example\.com|mailinator\.com|trashmail\.|guerrillamail\.|10minutemail\.|tempmail\.|dispostable\.|yopmail\.)/i', $email)) {
                $reasons[] = 'Email jetable ou domaine de test';
            }
        }

        // Rule 9: Suspicious TLDs (.ru, .su, .xyz, .top, .click, etc.)
        if (!empty($criteria['check_tlds'])) {
            if (preg_match('/\.(ru|su|xyz|top|click|link|work|loan|date)$/i', $email)) {
                $tld = substr(strrchr($email, '.'), 1);
                $reasons[] = 'Extension de domaine à haut risque (.' . $tld . ')';
            }
        }

        // Rule 10: Randomized email username (e.g. ffuexvizct85@hotmail.com)
        if (!empty($criteria['check_random_email'])) {
            $emailUser = strtolower(strstr($email, '@', true));
            if (preg_match('/^[bcdfghjklmnpqrstvwxz]{7,15}[0-9]{2,4}$/', $emailUser)) {
                $reasons[] = 'Adresse email générée par bot (' . $emailUser . ')';
            }
        }

        return !empty($reasons);
    }

    /**
     * Scan all customers and return detected spam accounts with details
     *
     * @param array $criteria
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function scan($criteria = null, $limit = 0, $offset = 0)
    {
        if ($criteria === null) {
            $criteria = self::getDefaultCriteria();
        }

        $db = Db::getInstance();
        $sql = '
            SELECT c.id_customer, c.firstname, c.lastname, c.email, c.date_add, c.active,
                   (SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'address` a WHERE a.id_customer = c.id_customer) as nb_addr,
                   (SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'orders` o WHERE o.id_customer = c.id_customer) as nb_orders
            FROM `' . _DB_PREFIX_ . 'customer` c
            ORDER BY c.id_customer DESC
        ';

        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $offset . ', ' . (int) $limit;
        }

        $customers = $db->executeS($sql);
        if (!$customers) {
            return [
                'total_scanned' => 0,
                'spam_count' => 0,
                'clean_count' => 0,
                'spam_with_addr' => 0,
                'items' => [],
            ];
        }

        $items = [];
        $spamCount = 0;
        $cleanCount = 0;
        $spamWithAddr = 0;

        foreach ($customers as $c) {
            $reasons = [];
            if (self::isSpam($c, $reasons, $criteria)) {
                $spamCount++;
                if ((int) $c['nb_addr'] > 0) {
                    $spamWithAddr++;
                }
                $items[] = [
                    'id_customer' => (int) $c['id_customer'],
                    'firstname' => $c['firstname'],
                    'lastname' => $c['lastname'],
                    'email' => $c['email'],
                    'date_add' => $c['date_add'],
                    'nb_addr' => (int) $c['nb_addr'],
                    'nb_orders' => (int) $c['nb_orders'],
                    'reasons' => $reasons,
                ];
            } else {
                $cleanCount++;
            }
        }

        return [
            'total_scanned' => count($customers),
            'spam_count' => $spamCount,
            'clean_count' => $cleanCount,
            'spam_with_addr' => $spamWithAddr,
            'items' => $items,
        ];
    }

    /**
     * Get all detected spam IDs for mass batch deletion
     *
     * @param array $criteria
     * @return array Array of customer IDs
     */
    public static function getSpamIds($criteria = null)
    {
        $scan = self::scan($criteria);
        $ids = [];
        foreach ($scan['items'] as $item) {
            $ids[] = $item['id_customer'];
        }
        return $ids;
    }

    /**
     * Delete a batch of customer IDs cleanly with relational tables
     *
     * @param array $customerIds
     * @param bool $deleteAddresses
     * @return array Stats on deleted records
     */
    public static function deleteBatch($customerIds, $deleteAddresses = true)
    {
        if (empty($customerIds)) {
            return ['customers_deleted' => 0, 'addresses_deleted' => 0, 'carts_deleted' => 0];
        }

        $db = Db::getInstance();
        $sanitizedIds = array_map('intval', $customerIds);
        $sanitizedIds = array_filter($sanitizedIds, function ($id) {
            return $id > 0;
        });

        if (empty($sanitizedIds)) {
            return ['customers_deleted' => 0, 'addresses_deleted' => 0, 'carts_deleted' => 0];
        }

        $idList = implode(',', $sanitizedIds);

        // Free vs PRO quota check
        if (!self::isPro()) {
            $remaining = self::getRemainingFreeDeletions();
            if ($remaining <= 0) {
                return [
                    'success' => false,
                    'upgrade_required' => true,
                    'error' => 'Limite de la version gratuite atteinte (' . self::FREE_DELETE_LIMIT . ' suppressions). Débloquez la version PRO pour un nettoyage illimité.',
                    'customers_deleted' => 0,
                    'addresses_deleted' => 0,
                    'carts_deleted' => 0,
                ];
            }
            if (count($sanitizedIds) > $remaining) {
                $sanitizedIds = array_slice($sanitizedIds, 0, $remaining);
                $idList = implode(',', $sanitizedIds);
            }
        }

        // Security check: NEVER delete any customer who has an order in ps_orders
        $orderedCustomerIds = $db->executeS('
            SELECT DISTINCT id_customer 
            FROM `' . _DB_PREFIX_ . 'orders` 
            WHERE id_customer IN (' . $idList . ')
        ');
        if (!empty($orderedCustomerIds)) {
            $protectedIds = array_column($orderedCustomerIds, 'id_customer');
            $sanitizedIds = array_diff($sanitizedIds, $protectedIds);
            if (empty($sanitizedIds)) {
                return ['customers_deleted' => 0, 'addresses_deleted' => 0, 'carts_deleted' => 0, 'protected' => count($protectedIds)];
            }
            $idList = implode(',', $sanitizedIds);
        }

        $addressesDeleted = 0;
        $cartsDeleted = 0;

        // 1. Delete associated addresses if requested (that are not tied to existing orders)
        if ($deleteAddresses) {
            $addrQuery = '
                SELECT id_address FROM `' . _DB_PREFIX_ . 'address` 
                WHERE id_customer IN (' . $idList . ')
                AND id_address NOT IN (SELECT id_address_delivery FROM `' . _DB_PREFIX_ . 'orders`)
                AND id_address NOT IN (SELECT id_address_invoice FROM `' . _DB_PREFIX_ . 'orders`)
            ';
            $addrRows = $db->executeS($addrQuery);
            if (!empty($addrRows)) {
                $addrIds = array_column($addrRows, 'id_address');
                $addrIdList = implode(',', array_map('intval', $addrIds));
                $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'address` WHERE id_address IN (' . $addrIdList . ')');
                $addressesDeleted = count($addrIds);
            }
        }

        // 2. Delete associated empty / orphan carts
        $cartQuery = '
            SELECT id_cart FROM `' . _DB_PREFIX_ . 'cart` 
            WHERE id_customer IN (' . $idList . ')
            AND id_cart NOT IN (SELECT id_cart FROM `' . _DB_PREFIX_ . 'orders`)
        ';
        $cartRows = $db->executeS($cartQuery);
        if (!empty($cartRows)) {
            $cartIds = array_column($cartRows, 'id_cart');
            $cartIdList = implode(',', array_map('intval', $cartIds));
            $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'cart_product` WHERE id_cart IN (' . $cartIdList . ')');
            $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'cart` WHERE id_cart IN (' . $cartIdList . ')');
            $cartsDeleted = count($cartIds);
        }

        // 3. Delete groups associations
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_group` WHERE id_customer IN (' . $idList . ')');

        // 4. Delete customer threads and messages
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_message` WHERE id_customer_thread IN (
            SELECT id_customer_thread FROM `' . _DB_PREFIX_ . 'customer_thread` WHERE id_customer IN (' . $idList . ')
        )');
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_thread` WHERE id_customer IN (' . $idList . ')');

        // 5. Delete from customer table
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'customer` WHERE id_customer IN (' . $idList . ')');
        $customersDeleted = count($sanitizedIds);

        // Track free usage count
        if (!self::isPro()) {
            $deletedSoFar = (int) Configuration::get('SCC_FREE_DELETED_COUNT', 0);
            Configuration::updateValue('SCC_FREE_DELETED_COUNT', $deletedSoFar + $customersDeleted);
        }

        return [
            'success' => true,
            'customers_deleted' => $customersDeleted,
            'addresses_deleted' => $addressesDeleted,
            'carts_deleted' => $cartsDeleted,
            'remaining_free' => self::getRemainingFreeDeletions(),
            'is_pro' => self::isPro(),
        ];
    }

    /**
     * Scan contact form spam threads
     */
    public static function scanContactSpam()
    {
        $db = Db::getInstance();
        $threads = $db->executeS('
            SELECT ct.id_customer_thread, ct.email, ct.date_add, cm.message
            FROM `' . _DB_PREFIX_ . 'customer_thread` ct
            JOIN `' . _DB_PREFIX_ . 'customer_message` cm ON ct.id_customer_thread = cm.id_customer_thread
            WHERE cm.message REGEXP "http|www\\.|talkwithwebvisitor|seo-package|dating|viagra|casino|traffic|crypto|porn|visitor"
               OR ct.email LIKE "%.ru" OR ct.email LIKE "%.xyz" OR ct.email LIKE "%.top"
            GROUP BY ct.id_customer_thread
            ORDER BY ct.id_customer_thread DESC
        ');
        return $threads ? $threads : [];
    }

    /**
     * Delete contact form spam threads
     */
    public static function deleteContactSpamBatch($threadIds)
    {
        if (empty($threadIds)) {
            return 0;
        }
        $db = Db::getInstance();
        $sanitizedIds = implode(',', array_map('intval', $threadIds));
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_message` WHERE id_customer_thread IN (' . $sanitizedIds . ')');
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_thread` WHERE id_customer_thread IN (' . $sanitizedIds . ')');
        return count($threadIds);
    }

    /**
     * Run automated CRON maintenance
     */
    public static function runCron()
    {
        if (!self::isPro()) {
            return ['success' => false, 'error' => 'La fonction Pilote Automatique (CRON) nécessite la version PRO.'];
        }

        $criteria = self::getDefaultCriteria();
        $scan = self::scan($criteria);
        $totalSpam = count($scan['items']);

        $deletedCust = 0;
        $deletedAddr = 0;
        $deletedCarts = 0;

        if ($totalSpam > 0) {
            $ids = array_column($scan['items'], 'id_customer');
            $chunks = array_chunk($ids, 250);
            foreach ($chunks as $chunk) {
                $res = self::deleteBatch($chunk, true);
                $deletedCust += $res['customers_deleted'];
                $deletedAddr += $res['addresses_deleted'];
                $deletedCarts += $res['carts_deleted'];
            }
        }

        // Also clean contact spam
        $contactSpams = self::scanContactSpam();
        $deletedContacts = 0;
        if (!empty($contactSpams)) {
            $cIds = array_column($contactSpams, 'id_customer_thread');
            $deletedContacts = self::deleteContactSpamBatch($cIds);
        }

        $summary = sprintf(
            '%d clients spam supprimés, %d adresses supprimées, %d messages contact nettoyés',
            $deletedCust,
            $deletedAddr,
            $deletedContacts
        );

        Configuration::updateValue('SCC_LAST_CRON_DATE', date('Y-m-d H:i:s'));
        Configuration::updateValue('SCC_LAST_CRON_STATS', $summary);

        return [
            'success' => true,
            'customers_deleted' => $deletedCust,
            'addresses_deleted' => $deletedAddr,
            'contact_deleted' => $deletedContacts,
            'summary' => $summary,
            'date' => date('Y-m-d H:i:s')
        ];
    }
}
