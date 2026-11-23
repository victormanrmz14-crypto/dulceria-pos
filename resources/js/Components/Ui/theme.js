// Local tokens only: tenant settings and Bootstrap's global theme stay intact.
export function premiumTheme(colors) {
    const brand = /^#[\da-f]{6}$/i.test(colors?.primario || '') ? colors.primario : '#8B0000';
    const luminanceOf = (hex) => hex.slice(1).match(/../g).map((v) => parseInt(v, 16) / 255)
        .map((v) => v <= .04045 ? v / 12.92 : ((v + .055) / 1.055) ** 2.4)
        .reduce((sum, v, i) => sum + v * [.2126, .7152, .0722][i], 0);
    const luminance = luminanceOf(brand);
    const whiteText = 1.05 / (luminance + .05) >= 4.5;
    const darkText = (luminance + .05) / (luminanceOf('#202126') + .05) >= 4.5 ? '#202126' : '#000000';
    return {
        '--p-brand': brand,
        '--p-on-brand': whiteText ? '#ffffff' : darkText,
        '--p-brand-ink': whiteText ? brand : '#202126',
        '--p-brand-hover-mix': whiteText ? '#000000' : '#ffffff',
    };
}
