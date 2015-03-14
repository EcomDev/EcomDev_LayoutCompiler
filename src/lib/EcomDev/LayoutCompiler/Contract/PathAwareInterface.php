<?php

/**
 * Interface for objects that should have information about save path
 *
 */
interface EcomDev_LayoutCompiler_Contract_PathAwareInterface
{
    /**
     * Sets a save path for a compiled layout files
     *
     * @param string $savePath
     * @return $this
     */
    public function setSavePath($savePath);

    /**
     * Returns a current save path for a compiler
     *
     * @return string
     */
    public function getSavePath();
}