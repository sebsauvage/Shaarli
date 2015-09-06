<?php
/**
 * Testing the untestable - Session ID generation
 */
class ReferenceSessionIdHashes
{
    // Session ID hashes
    protected static $sidHashes = null;

    /**
     * Generates session ID hashes for all algorithms & bit representations
     */
    public static function genAllHashes()
    {
        foreach (hash_algos() as $algo) {
            self::$sidHashes[$algo] = array();

            foreach (array(4, 5, 6) as $bpc) {
                self::$sidHashes[$algo][$bpc] = self::genSidHash($algo, $bpc);
            }
        }
    }

    /**
     * Generates a session ID for a given hash algorithm and bit representation
     *
     * @param string $function           name of the hash function
     * @param int    $bits_per_character representation type
     *
     * @return string the generated session ID
     */
    protected static function genSidHash($function, $bits_per_character)
    {
        if (session_id()) {
            session_destroy();
        }

        ini_set('session.hash_function', $function);
        ini_set('session.hash_bits_per_character', $bits_per_character);

        session_start();
        return session_id();
    }

    /**
     * Returns the reference hash array
     *
     * @return array session IDs generated for all available algorithms and bit
     *               representations
     */
    public static function getHashes()
    {
        return self::$sidHashes;
    }
}
