export default () => {
    const TW_HUES = [
        "slate",
        "gray",
        "zinc",
        "neutral",
        "stone",
        "red",
        "orange",
        "amber",
        "yellow",
        "lime",
        "green",
        "emerald",
        "teal",
        "cyan",
        "sky",
        "blue",
        "indigo",
        "violet",
        "purple",
        "fuchsia",
        "pink",
        "rose",
    ];
    const TW_SATURATIONS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];

    const contrast = (background) => {
        if (!background.match(/-/)) {
            return background === "white" ? "black" : "white";
        } else {
            const [_, shade] = background.split(/-/);

            return parseInt(shade) < 500 ? "black" : "white";
        }
    };

    return { contrast };
};
