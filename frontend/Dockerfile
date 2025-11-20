FROM node:22-alpine AS build

WORKDIR /app

# Copy package files
COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./

# Install dependencies and fix permissions immediately
RUN if [ -f package-lock.json ]; then \
        npm ci && chmod -R 755 node_modules/.bin; \
    elif [ -f pnpm-lock.yaml ]; then \
        npm install -g pnpm && pnpm install --frozen-lockfile && chmod -R 755 node_modules/.bin; \
    elif [ -f yarn.lock ]; then \
        yarn install --frozen-lockfile && chmod -R 755 node_modules/.bin; \
    else \
        npm install && chmod -R 755 node_modules/.bin; \
    fi

# Copy application source
COPY . .

# Build application
RUN npm run build

FROM nginx:1.27-alpine

COPY --from=build /app/dist /usr/share/nginx/html

# Copy nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]


