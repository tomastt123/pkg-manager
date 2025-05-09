# Personal Knowledge‑Graph Manager

## An AI‑powered web application to ingest, analyze, and visualize knowledge from arbitrary URLs (articles, videos, PDFs, etc.) in a collaborative, real‑time graph interface. Work in progress.

## Introduction

The Personal Knowledge‑Graph Manager helps you build your own knowledge base by:

Ingesting URLs as Document entities

Fetching raw content in the background via Symfony Messenger

Extracting entities & relationships using NLP/AI

Storing knowledge in a graph database (Neo4j)

Exposing a GraphQL API for rich queries

Visualizing an interactive force‑directed graph in React

Collaborating in real time with Mercure (WebSockets)

This end‑to‑end pipeline turns unstructured web content into a structured, explorable knowledge graph—perfect for researchers, students, or anyone wanting to map out concepts and their connections.

## Tech Stack

### Backend

PHP 8.2‑FPM with Symfony 6

API Platform (REST + GraphQL endpoint)

Symfony Messenger (background jobs)

Symfony HttpClient (fetch external URLs)

Doctrine ORM (relational mapping)

Queue & Real‑Time

Redis (Messenger transport)

Mercure (real‑time updates via JWT‑secured WebSockets)

Graph Storage

Neo4j AuraDB Free (nodes & relationships)

### Frontend

React (Create React App or Vite)

Apollo Client (GraphQL queries & caching)

react‑force‑graph (force‑directed graph visualization)

Containerization & DevOps

Docker Compose (multi‑service orchestration)

api/ (Symfony + PHP‑FPM + Nginx)

redis (job queue)

neo4j (graph database)

mercure (real‑time hub)

GitHub Actions (CI: lint, tests, Docker build)

## Next Steps (Development Milestone)

Configure var/ as a Docker volume to avoid read‑only issues on host bind mounts.

Implement the NLP extraction pipeline (spaCy or OpenAI).

Sync entities/relations into Neo4j and expose via GraphQL.

Build the React graph UI and connect via Mercure for live collaboration.

Add authentication & fine‑grained ACLs per graph node.
