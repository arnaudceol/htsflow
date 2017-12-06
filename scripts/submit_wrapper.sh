#!/bin/bash

source activate htsflow

export HTSFLOW_CONF=/data/BA/htsflowNP/conf/htsflow.ini

cd /data/BA/htsflowNP/pipeline/
Rscript  ../scripts/HTSflowSubmitter.R

