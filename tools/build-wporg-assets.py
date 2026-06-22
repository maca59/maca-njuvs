#!/usr/bin/env python3
"""Build WordPress.org plugin directory assets (banner, icon, screenshots)."""

from __future__ import annotations

import os
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "assets"
OUT = ROOT / "wordpress-org" / "assets"

SCREENSHOT_MAX_WIDTH = 1280
PNG_OPTS = {"optimize": True, "compress_level": 9}


def save_png(image: Image.Image, path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    image.save(path, format="PNG", **PNG_OPTS)
    print(f"  {path.name}: {image.size[0]}x{image.size[1]} ({path.stat().st_size // 1024} KB)")


def fit_width(image: Image.Image, max_width: int) -> Image.Image:
    if image.width <= max_width:
        return image

    ratio = max_width / image.width
    size = (max_width, max(1, round(image.height * ratio)))
    return image.resize(size, Image.Resampling.LANCZOS)


def build_banner() -> None:
    source = SRC / "banner.png"
    if not source.is_file():
        raise FileNotFoundError(f"Missing {source}")

    banner = Image.open(source).convert("RGB")
    save_png(banner.resize((772, 250), Image.Resampling.LANCZOS), OUT / "banner-772x250.png")
    save_png(banner.resize((1544, 500), Image.Resampling.LANCZOS), OUT / "banner-1544x500.png")


def build_icons() -> None:
    source = SRC / "maca-njuvs_ikon.png"
    if not source.is_file():
        raise FileNotFoundError(f"Missing {source}")

    icon = Image.open(source).convert("RGBA")
    save_png(icon.resize((128, 128), Image.Resampling.LANCZOS), OUT / "icon-128x128.png")
    save_png(icon.resize((256, 256), Image.Resampling.LANCZOS), OUT / "icon-256x256.png")


def build_screenshots() -> None:
    for index in range(1, 7):
        source = SRC / f"screenshot-{index}.png"
        if not source.is_file():
            raise FileNotFoundError(f"Missing {source}")

        shot = Image.open(source)
        if shot.mode not in ("RGB", "RGBA"):
            shot = shot.convert("RGBA")
        shot = fit_width(shot, SCREENSHOT_MAX_WIDTH)
        save_png(shot, OUT / f"screenshot-{index}.png")


def main() -> None:
    print(f"Source: {SRC}")
    print(f"Output: {OUT}\n")

    print("Banner:")
    build_banner()
    print("Icons:")
    build_icons()
    print("Screenshots:")
    build_screenshots()
    print("\nDone.")


if __name__ == "__main__":
    main()
