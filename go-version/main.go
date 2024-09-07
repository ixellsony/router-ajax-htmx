package main

import (
	"fmt"
	"html/template"
	"io/ioutil"
	"log"
	"net/http"
	"strings"
)

var routes = map[string]string{
	"":        "home.html",
	"page1":   "page1.html",
	"page2":   "page2.html",
	"contact": "contact.html",
}

func main() {
	http.HandleFunc("/", handleRequest)
	fmt.Println("Server is running on http://localhost:8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func handleRequest(w http.ResponseWriter, r *http.Request) {
	path := strings.Trim(r.URL.Path, "/")
	filename, exists := routes[path]
	if !exists {
		w.WriteHeader(http.StatusNotFound)
		filename = "404.html"
	}

	content, err := ioutil.ReadFile(filename)
	if err != nil {
		http.Error(w, "Internal Server Error", http.StatusInternalServerError)
		return
	}

	if r.Header.Get("HX-Request") != "" {
		w.Write(content)
		return
	}

	tmpl, err := template.New("layout").Parse(`
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon App</title>
    <script src="https://unpkg.com/htmx.org@1.9.2" integrity="sha384-L6OqL9pRWyyFU3+/bjdSri+iIphTN/bvYyM37tICVyOJkWZLpP2vGn6VUEXgzg6h" crossorigin="anonymous"></script>
</head>
<body>
    <nav>
        <a href="/" hx-get="/" hx-swap="innerHTML" hx-trigger="click" hx-target="#content" hx-push-url="true">Home</a>
        <a href="/page1" hx-get="/page1" hx-swap="innerHTML" hx-trigger="click" hx-target="#content" hx-push-url="true">Page1</a>
    </nav>
    <div id="content">
        {{.Content}}
    </div>
</body>
</html>
	`)
	if err != nil {
		http.Error(w, "Internal Server Error", http.StatusInternalServerError)
		return
	}

	data := struct {
		Content template.HTML
	}{
		Content: template.HTML(content),
	}

	err = tmpl.Execute(w, data)
	if err != nil {
		http.Error(w, "Internal Server Error", http.StatusInternalServerError)
		return
	}
}
