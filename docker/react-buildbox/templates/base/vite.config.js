// templates/base/vite.config.js
import {defineConfig} from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
    base: './',
    plugins: [react()],
    build: {outDir: "/patch/dist", emptyOutDir: true},
    resolve: {alias: {"@": "/src"}},
});
