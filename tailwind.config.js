import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/css/**/*.css",
        ,
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                forest: {
                    DEFAULT: "#2f4f4f", // 深森林綠 (主色)
                    dark: "#3D4E17",
                    canopy: "#4f7942", // 樹冠綠
                    moss: "#8a9a5b", // 苔蘚綠
                    bark: "#5c4438", // 樹皮棕
                    fern: "#6b8e23", // 羊齒植物綠
                    mist: "#dce3dc", // 森林霧白
                    leaf: "#3b7a57", // 葉片綠
                    soil: "#836953", // 土壤棕
                },
                floral: {
                    DEFAULT: "#b56576", // 玫瑰紅 (導覽列、主要深色)
                    plum: "#6d597a", // 深紫羅蘭 (標題、hover 狀態)
                    mauve: "#915f78", // 暗紫粉 (次要深色)

                    blossom: "#f5c6c8", // 櫻花粉
                    lilac: "#c3aed6", // 薰衣草紫
                    peach: "#f7d1ba", // 桃花粉橘
                    // mist: "#f2e9e4", // 花園霧白
                    petal: "#ffe5ec", // 花瓣淺粉
                    stem: "#a8d5ba", // 枝葉嫩綠
                },
                garden: {
                    DEFAULT: "#b6722d", // 琥珀橘棕 (主色，導覽列底色)
                    coffee: "#5a3825", // 深咖啡棕 (主色/導覽列)
                    olive: "#6b705c", // 橄欖綠
                    moss: "#8a9a5b", // 苔蘚綠
                    soil: "#836953", // 土壤棕
                    sunflower: "#e6b566", // 向日葵黃
                    peach: "#eeb97f", // 溫柔杏橘
                    // mist: "#f3ede4", // 淡霧米白 (背景)
                    cream: "#ffe6a7", // 奶油白 (hover/填充)
                },
            },
        },
    },

    plugins: [forms],
};
