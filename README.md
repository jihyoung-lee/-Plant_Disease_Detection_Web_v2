# -Plant_Disease_Detection_Web_v2
![Image](https://github.com/user-attachments/assets/0ef5a30c-204a-4389-8d92-5438ab63dce2)
![image](https://github.com/user-attachments/assets/dc8fc5ff-6484-4f5c-a57c-b4eaebdcf940)
![image](https://github.com/user-attachments/assets/88d1ba08-dd6c-4e1e-b962-e45231dd07d8)
![image](https://github.com/user-attachments/assets/dc220cce-afd7-4b4a-a877-7d8b24577a37)

laravel 10 , vue 3, s3 , redis
http://ncpms.rda.go.kr/ 
# Plant Disease Search System

This project is a web-based crop disease search system, built with a **fully decoupled architecture**:

* **Laravel 10 (PHP)** – Backend API server
* **Vue 3** – Frontend single-page application
* **Redis** – In-memory cache for external API responses

---

## ⚐ Architecture

* The frontend (Vue) and backend (Laravel) are **completely separated**.
* The frontend communicates with the backend via **RESTful API calls**.
* The backend communicates with a **public external API** to fetch crop disease data.
* To reduce latency and external API load, **Redis caching** is implemented.

---

## ⚡ Performance Optimization

* All external API responses are cached using **Redis**.
* Cached data is stored for **6 hours**.
* API responses are reused unless the cache is expired or manually cleared.
* This improves responsiveness and reduces unnecessary external API requests.

---

## 🤩 Redis Cache Key Structure

* Disease list cache:

  ```
  disease_list:{searchType}:{encodedSearchKeyword}
  ```

* Disease info cache:

  ```
  disease_info:{encodedCropName}:{encodedSickNameKor}
  ```

---

## 🎇 Example API Response

**GET** `/api/diseases?type=1&search=사과&page=1`

```json
{
  "data": [
    {
      "cropName": "사과",
      "sickNameKor": "검은별무늬병",
      "oriImg": "https://example.com/image.jpg"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 5,
    "total": 33,
    "last_page": 7
  }
}
```

---

## 🛠 Tech Stack

| Role         | Tech                    |
| ------------ | ----------------------- |
| Frontend     | Vue 3, Axios            |
| Backend      | Laravel 10+             |
| API Caching  | Redis                   |
| External API | Korean Agricultural API |
| Data Format  | JSON                    |

---

## 🔧 .env Sample (Backend)

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://127.0.0.1

# Redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# External API
RDA_API_KEY=your_api_key_here
RDA_API_ENDPOINT=https://api.nongsaro.go.kr/service/...
```
