<?php

trait EcomDev_LayoutCompiler_PathAwareTrait
{
    /**
     * @var string
     */
    private $savePath;

    /**
     * Sets a save path for a compiled layout files
     *
     * @param string $savePath
     * @return $this
     */
    public function setSavePath($savePath)
    {
        $this->savePath = $savePath;
        return $this;
    }

    /**
     * Returns a current save path for a compiler
     *
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }
}
