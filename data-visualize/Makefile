# Makefile for generating product combinations and balloon visualization

# Variables
PHP = php
SRC_DIR = src
DATA_FILE = ../Online\ Retail.xlsx
PRODUCT_COMBINATIONS = product_combinations.json
CHART_URL_FILE = chart_url.txt
CHART_PNG = balloon_chart.png

# Default target
.PHONY: all
all: visualization

# Generate product_combinations.json from Excel data
$(PRODUCT_COMBINATIONS): $(DATA_FILE) $(SRC_DIR)/quick_analyze.php
	@echo "Generating product combinations..."
	$(PHP) -d memory_limit=512M $(SRC_DIR)/quick_analyze.php

# Generate balloon visualization URL
$(CHART_URL_FILE): $(PRODUCT_COMBINATIONS) $(SRC_DIR)/visualize.php
	@echo "Generating balloon visualization..."
	$(PHP) $(SRC_DIR)/visualize.php > $(CHART_URL_FILE)
	@echo "Chart URL saved to $(CHART_URL_FILE)"

# Download the PNG from the generated URL
$(CHART_PNG): $(CHART_URL_FILE)
	@echo "Downloading balloon chart PNG..."
	@if command -v curl >/dev/null 2>&1; then \
		curl -L -o $(CHART_PNG) "$$(cat $(CHART_URL_FILE))"; \
	elif command -v wget >/dev/null 2>&1; then \
		wget -O $(CHART_PNG) "$$(cat $(CHART_URL_FILE))"; \
	else \
		echo "Error: Neither curl nor wget found. Please install one to download the chart."; \
		exit 1; \
	fi
	@echo "Balloon chart saved to $(CHART_PNG)"

# Main visualization target
.PHONY: visualization
visualization: $(CHART_PNG)
	@echo "Visualization complete!"
	@echo "Generated files:"
	@echo "  - $(PRODUCT_COMBINATIONS)"
	@echo "  - $(CHART_URL_FILE)" 
	@echo "  - $(CHART_PNG)"

# Clean generated files
.PHONY: clean
clean:
	@echo "Cleaning generated files..."
	rm -f $(PRODUCT_COMBINATIONS) $(CHART_URL_FILE) $(CHART_PNG) head.csv shape.csv

# Help target
.PHONY: help
help:
	@echo "Available targets:"
	@echo "  make all          - Generate product combinations and balloon visualization (default)"
	@echo "  make visualization - Same as 'all'"
	@echo "  make clean        - Remove all generated files"
	@echo "  make help         - Show this help message"
	@echo ""
	@echo "The process:"
	@echo "  1. Analyzes Excel data to find product combinations"
	@echo "  2. Generates product_combinations.json"
	@echo "  3. Creates balloon visualization URL using QuickChart"
	@echo "  4. Downloads the visualization as balloon_chart.png"