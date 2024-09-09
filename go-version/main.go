package main

import (
	"html/template"
	"io"
	"log"
	"mime"
	"net/http"
	"os"
	"path/filepath"
	"strings"
)

// Route configuration
var routes = map[string]struct {
	File     string
	HTMXOnly bool
}{
	"":      {File: "pages/home.html", HTMXOnly: false},
	"page1": {File: "pages/page1.html", HTMXOnly: false},
	"login": {File: "pages/login.html", HTMXOnly: false},
}

// Handle 404 error
func handle404(w http.ResponseWriter) {
	w.WriteHeader(http.StatusNotFound)
	tmpl, _ := template.ParseFiles("404.html")
	tmpl.Execute(w, nil)
}

// Serve static media files
func serveMedia(w http.ResponseWriter, r *http.Request) {
	mediaPath := strings.TrimPrefix(r.URL.Path, "/media/")
	if _, err := os.Stat(mediaPath); err == nil {
		mimeType := mime.TypeByExtension(filepath.Ext(mediaPath))
		w.Header().Set("Content-Type", mimeType)
		http.ServeFile(w, r, mediaPath)
	} else {
		handle404(w)
	}
}

// Route handler
func routeHandler(w http.ResponseWriter, r *http.Request) {
	currentRoute := strings.Trim(r.URL.Path, "/")

	// Check if the route is part of 'media' folder
	if strings.HasPrefix(currentRoute, "media/") {
		serveMedia(w, r)
		return
	}

	// Check if route exists
	route, exists := routes[currentRoute]
	if !exists {
		handle404(w)
		return
	}

	// Check HTMX only request
	if route.HTMXOnly && r.Header.Get("HX-Request") == "" {
		handle404(w)
		return
	}

	// Load the template content
	tmpl, err := template.ParseFiles(route.File)
	if err != nil {
		log.Printf("Error loading file %s: %v", route.File, err)
		handle404(w)
		return
	}

	// Render content
	var content strings.Builder
	if err := tmpl.Execute(&content, nil); err != nil {
		log.Printf("Error rendering template: %v", err)
		handle404(w)
		return
	}

	// HTMX request: return content only
	if r.Header.Get("HX-Request") != "" {
		io.WriteString(w, content.String())
		return
	}

	// Full page rendering for non-HTMX requests
	fullPageTemplate, _ := template.ParseFiles("layout.html")
	fullPageTemplate.Execute(w, map[string]string{
		"Content": content.String(),
	})
}

func main() {
	// Define route handler
	http.HandleFunc("/", routeHandler)

	// Start server
	log.Println("Server started at :8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}
