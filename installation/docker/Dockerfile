FROM continuumio/miniconda2:4.5.4

MAINTAINER Arnaud Ceol <arnaud.ceol.pro@gmail.com>


RUN conda config --add channels conda-forge
RUN conda config --add channels bioconda
RUN conda config --add channels r


COPY conda-requirements.txt .
COPY python-requirements.txt .

RUN conda install -c bioconda --yes --file conda-requirements.txt
RUN pip install command

# Set default behavior to run the command and get help.
ENTRYPOINT ["conda"]
CMD [" list"]

