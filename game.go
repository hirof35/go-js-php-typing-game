package main

import (
	"encoding/json"
	"fmt"
	"net/http"
)

type ScoreRequest struct {
	Time float64 `json:"time"`
}

func main() {
	// 1. 単語リストを返すAPI
	http.HandleFunc("/words", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		response := map[string][]string{
			"words": {"golang", "php", "javascript", "html", "typing"},
		}
		json.NewEncoder(w).Encode(response)
	})

	// 2. スコアを受け取って結果メッセージを返すAPI
	http.HandleFunc("/score", func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost {
			http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
			return
		}

		var req ScoreRequest
		if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
			http.Error(w, err.Error(), http.StatusBadRequest)
			return
		}

		// Go側で判定ロジックを処理
		message := "ナイスタイピング！PHPとGoの連携成功です！"
		if req.Time < 12.0 {
			message = "超高速！Goの並行処理並みのスピードです！"
		}

		w.Header().Set("Content-Type", "application/json")
		response := map[string]interface{}{
			"status":  "success",
			"time":    req.Time,
			"message": message,
		}
		json.NewEncoder(w).Encode(response)
	})

	fmt.Println("Go API Server running on http://localhost:8081")
	http.ListenAndServe(":8081", nil)
}